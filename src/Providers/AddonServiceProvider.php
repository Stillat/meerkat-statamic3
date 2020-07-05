<?php

namespace Stillat\Meerkat\Providers;

use Illuminate\Support\Str;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Configuration\Manager;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Statamic\Providers\AddonServiceProvider as StatamicAddonServiceProvider;
use Statamic\Statamic;
use Stillat\Meerkat\Http\RequestHelpers;
use Stillat\Meerkat\PathProvider;

/**
 * Class AddonServiceProvider
 * @package Stillat\Meerkat\Providers
 */
class AddonServiceProvider extends StatamicAddonServiceProvider
{

    /**
     * Indicates whether or not the addon's language files have already been loaded into the application.
     *
     * @var bool Whether or not the addon language has already been loaded.
     */
    public static $langIncluded = false;

    /**
     * The request contexts this service provider should respond to.
     *
     * @var array The list of contexts.
     */
    protected $contexts = [];

    /**
     * A collection of additional providers to boot and register for this addon.
     *
     * @var array Additional providers to boot and register.
     */
    protected $providers = [];

    /**
     * A collection of view composers that should automatically be registered.
     *
     * @var array The view composers.
     */
    protected $composers = [];

    /**
     * Indicates if the context state has resolved.
     *
     * @var bool
     */
    private $hasResoledContext = false;

    /**
     * Contains the contexts that were resolved for the current request.
     *
     * @var array The request contexts.
     */
    private $requestContexts = [];

    /**
     * Indicates whether to defer key actions until Statamic has booted.
     *
     * @var bool Whether to wait until Statamic boots.
     */
    protected $defer = true;

    /**
     * A collection of configuration items to publish to the Statamic installation.
     *
     * @var array The config entries to publish.
     */
    protected $config = [];

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Ensures that the Meerkat configuration is available in the Statamic installation.
     */
    private function publishAddonConfiguration()
    {
        if (!is_array($this->config) || count($this->config) == 0) {
            return;
        }

        $configurationMapping = [];

        // Protects against files not existing.
        foreach ($this->config as $sourceConfig => $targetConfig) {
            if (!file_exists($sourceConfig) || is_dir($sourceConfig)) {
                continue;
            }

            $configurationMapping[$sourceConfig] = $targetConfig;
        }

        foreach ($configurationMapping as $sourceConfig => $targetConfig) {
            if (!file_exists($targetConfig)) {
                $dirName = dirname($targetConfig);

                if (!file_exists($dirName)) {
                    mkdir($dirName, 644, true);
                }

                copy($sourceConfig, $targetConfig);
            }
        }
    }

    /**
     * Provides a place for developers to place anything
     * that should be done before boot() is executed.
     */
    protected function beforeBoot()
    {
        // Just an empty method.
    }

    /**
     * Boots the current provider and all additional providers, if required.
     */
    public function boot()
    {
        $this->beforeBoot();

        if ($this->defer == false) {
            parent::boot();
        } else {
            Statamic::booted(function () {
                parent::boot();
            });
        }

        foreach ($this->providers as $provider) {
            $providerInstance = app($provider);

            if ($providerInstance !== null) {
                if ($providerInstance instanceof AddonServiceProvider) {
                    if ($this->isServiceProviderUsable($providerInstance)) {
                        $providerInstance->beforeBoot();
                        $providerInstance->boot();
                    }
                } else {
                    $providerInstance->boot();
                }
            }
        }

        $this->bootViewComposers();

        Statamic::booted(function () {
            $this->publishAddonConfiguration();
        });

        $this->includeAddonLanguages();
    }

    /**
     * Determines whether the provided service provider can be used in the current context.
     *
     * @param $serviceProvider ServiceProvider|AddonServiceProvider The provider instance to check.
     * @return bool
     */
    private function isServiceProviderUsable($serviceProvider)
    {
        $this->resolveContext();

        if ($serviceProvider instanceof AddonServiceProvider === false) {
            $this->hasResoledContext = true;
            return true;
        }

        $providerContexts = $serviceProvider->getContexts();

        if (is_array($providerContexts) == false || count($providerContexts) == 0) {
            return true;
        }

        $isProviderUsable = false;

        foreach ($this->requestContexts as $context) {
            if (in_array($context, $providerContexts)) {
                $isProviderUsable = true;
                break;
            }
        }

        return $isProviderUsable;
    }

    /**
     * Resolves the current request context (web, control panel, CLI, etc.)
     */
    private function resolveContext()
    {
        if ($this->hasResoledContext == true) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->requestContexts[] = 'cli';
        } else {
            $currentRequest = request();

            if ($currentRequest !== null) {
                if (RequestHelpers::isControlPanelRequest($currentRequest)) {
                    $this->requestContexts[] = 'cp';
                } else {
                    $this->requestContexts[] = 'web';
                }
            }
        }

        $this->hasResoledContext = true;
    }

    /**
     * Returns the contexts that the service provider should respond to.
     *
     * @return array The contexts.
     */
    public function getContexts()
    {
        return $this->contexts;
    }

    /**
     * Registers any defined view composers with the instance.
     */
    private function bootViewComposers()
    {
        if (is_array($this->composers) && count($this->composers) > 0) {
            foreach ($this->composers as $partial => $composer) {
                view()->composer($partial, $composer);
            }
        }
    }

    /**
     * Loads addon language translations into the application instance.
     */
    private function includeAddonLanguages()
    {
        if (AddonServiceProvider::$langIncluded || $this->app == null) {
            return;
        }

        $langDirectory = PathProvider::getResourcesDirectory('lang');

        if (file_exists($langDirectory) && is_dir($langDirectory)) {
            $this->loadTranslationsFrom($langDirectory, Addon::CODE_ADDON_NAME);
        }

        AddonServiceProvider::$langIncluded = true;
    }

    /**
     * Registers the current provider and all additional providers, if required.
     */
    public function register()
    {
        parent::register();

        foreach ($this->providers as $provider) {
            $providerInstance = app($provider);

            if ($providerInstance !== null) {
                if ($providerInstance instanceof AddonServiceProvider) {
                    if ($this->isServiceProviderUsable($providerInstance)) {
                        $providerInstance->register();
                    }
                } else {
                    $providerInstance->register();
                }
            }
        }
    }

}