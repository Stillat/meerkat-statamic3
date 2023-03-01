<?php

namespace Stillat\Meerkat\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Statamic\Providers\AddonServiceProvider as StatamicAddonServiceProvider;
use Statamic\Statamic;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Configuration\Manager;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Support\Str;
use Stillat\Meerkat\Http\RequestHelpers;
use Stillat\Meerkat\PathProvider;
use Stillat\Meerkat\Statamic\ControlPanel\TranslationEmitter;
use Stillat\Meerkat\Translation\LanguagePatcher;

/**
 * Class AddonServiceProvider
 *
 * Provides additional features for registering Statamic Addon services and features.
 *
 * @since 2.0.0
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

    /**
     * A collection of supplemental configuration items to publish to the Statamic installation.
     *
     * @var array
     */
    protected $supplementalConfiguration = [];

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

    public function __construct(Application $app)
    {
        parent::__construct($app);
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
            /** @var Manager $configManager */
            $configManager = app(Manager::class);
            $configManager->loadConfiguration();
            $this->publishAddonControlPanelAssets();
            $this->publishControlPanelTranslationPatches();
        });

        $this->includeAddonLanguages();
        $this->afterBoot();
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
     * Provides a place for developers to place anything
     * that should be done after boot() is executed.
     */
    protected function afterBoot()
    {
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
     * Ensures that the Meerkat configuration is available in the Statamic installation.
     */
    private function publishAddonConfiguration()
    {
        if (! is_array($this->config) || count($this->config) == 0) {
            return;
        }

        $configurationMapping = [];

        // Protects against files not existing.
        foreach ($this->config as $sourceConfig => $targetConfig) {
            if (! file_exists($sourceConfig) || is_dir($sourceConfig)) {
                continue;
            }

            $configurationMapping[$sourceConfig] = $targetConfig;
        }

        foreach ($configurationMapping as $sourceConfig => $targetConfig) {
            if (! file_exists($targetConfig)) {
                $dirName = dirname($targetConfig);

                if (! file_exists($dirName)) {
                    mkdir($dirName, 0755, true);
                }

                copy($sourceConfig, $targetConfig);
            }
        }

        foreach ($this->supplementalConfiguration as $configFile) {
            $dirname = dirname($configFile);

            if (Str::endsWith($dirname, '/') === false) {
                $dirname .= '/';
            }

            if (! file_exists($dirname)) {
                mkdir($dirname, 0755, true);
            }

            if (! file_exists($configFile)) {
                file_put_contents($configFile, '');
            }
        }

        $userConfigDirectory = config_path('meerkat/users/');

        if (! file_exists($userConfigDirectory)) {
            mkdir($userConfigDirectory);
        }
    }

    /**
     * Attempts to automatically publish the addon's control panel assets.
     */
    private function publishAddonControlPanelAssets()
    {
        $this->publishResourceAssets([
            '/dist/js' => '/js',
            '/dist/css' => '/css',
        ]);
    }

    /**
     * Publishes the specified resources.
     *
     * @param  array  $assets The resource asset mapping.
     */
    private function publishResourceAssets($assets)
    {
        foreach ($assets as $source => $target) {
            $resourceSource = PathProvider::getResourcesDirectory($source);
            $resourceTarget = public_path('/vendor/'.Addon::CODE_ADDON_NAME.$target);

            $resourceTarget = \Illuminate\Support\Str::finish($resourceTarget, '/');

            $this->publishResourceDirectory($resourceSource, $resourceTarget);
        }
    }

    private function publishControlPanelTranslationPatches()
    {
        $currentLocale = config('app.locale', 'en');

        $targetLocation = public_path('/vendor/'.Addon::CODE_ADDON_NAME.'/js/'.Addon::VERSION.'/'.$currentLocale.'_translations.js');

        if (! file_exists($targetLocation)) {
            /** @var LanguagePatcher $languagePatcher */
            $languagePatcher = app(LanguagePatcher::class);
            $statements = TranslationEmitter::getStatements($languagePatcher->getPatches());

            file_put_contents($targetLocation, $statements);
        }
    }

    /**
     * Publishes the assets in the source directory to the target directory.
     *
     * @param  string  $sourceDirectory The source directory.
     * @param  string  $targetDirectory The target directory.
     */
    private function publishResourceDirectory($sourceDirectory, $targetDirectory)
    {
        $publicPath = $targetDirectory.Addon::VERSION;
        $currentVersions = Paths::getDirectories($targetDirectory);
        $didFindCurrentVersion = false;
        $versionsToCleanUp = [];

        foreach ($currentVersions as $assetPath) {
            if (Str::endsWith($assetPath, Addon::VERSION)) {
                $didFindCurrentVersion = true;
            } else {
                $versionsToCleanUp[] = $assetPath;
            }
        }

        // If the current version is a development version
        // we will always publish any new asset changes.
        if (Str::endsWith(Addon::VERSION, '-dev')) {
            $didFindCurrentVersion = false;
        }

        if ($didFindCurrentVersion === false) {
            // First, publish new versions.
            if (file_exists($publicPath) == false) {
                mkdir($publicPath, 0755, true);
            }

            Paths::recursivelyCopyDirectory($sourceDirectory, $publicPath, false);
        }

        foreach ($versionsToCleanUp as $assetPath) {
            Paths::recursivelyRemoveDirectory($assetPath);
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
            /** @var ServiceProvider $providerInstance */
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

    /**
     * Attempts to locate the preferred resource path.
     *
     * @param  string  $resourceName The desired resource name.
     * @return string
     */
    public static function getResourceJavaScriptPath($resourceName)
    {
        if (Str::endsWith($resourceName, ['bootstrap', 'translations'])) {
            return $resourceName;
        }

        //if (Str::endsWith(Addon::VERSION, 'dev')) {
        //    return $resourceName;
        //}

        // $target = public_path('/vendor/' . Addon::CODE_ADDON_NAME . '/js/' . Addon::VERSION);
        // $minVersion = $resourceName . '.min.js';
        $minResource = $resourceName.'.min';

        return $minResource;

        // All builds should now be released with .min.js.
        /*if (file_exists($target . $minVersion)) {
            return $minResource;
        }

        return $resourceName;*/
    }
}
