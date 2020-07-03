<?php

namespace Stillat\Meerkat\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Providers\AddonServiceProvider as StatamicAddonServiceProvider;
use Stillat\Meerkat\Http\RequestHelpers;

/**
 * Class AddonServiceProvider
 * @package Stillat\Meerkat\Providers
 */
class AddonServiceProvider extends StatamicAddonServiceProvider
{


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

    public function __construct()
    {
        parent::__construct(app());
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
     * Boots the current provider and all additional providers, if required.
     */
    public function boot()
    {
        parent::boot();

        foreach ($this->providers as $provider) {
            $providerInstance = app($provider);

            if ($providerInstance !== null) {
                if ($providerInstance instanceof AddonServiceProvider) {
                    if ($this->isServiceProviderUsable($providerInstance)) {
                        $providerInstance->boot();
                    }
                } else {
                    $providerInstance->boot();
                }
            }
        }
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