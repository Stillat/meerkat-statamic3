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
     * A collection of additional providers to boot and register for this addon.
     *
     * @var array Additional providers to boot and register.
     */
    protected $providers = [];

    /**
     * Indicates if the Service Provider requires a Statamic Control Pnael request.
     *
     * @var bool
     */
    protected $requiresControlPanel = false;

    /**
     * Indicates if the Service Provider requires a Web request context.
     *
     * @var bool
     */
    protected $requiresWeb = false;

    /**
     * Indicates if the Service Provider requires the console context.
     *
     * @var bool
     */
    protected $requiresCli = false;

    /**
     * Indicates if the context state has resolved.
     *
     * @var bool
     */
    private $hasResoledContext = false;

    /**
     * Indicates if the current request is running in the Statamic Control Panel.
     *
     * @var bool
     */
    private $isRequestControlPanel = false;

    /**
     * Indicates if the current request is running a "web" request.
     *
     * @var bool
     */
    private $isRequestWeb = false;

    /**
     * Indicates whether the current request is running in the console.
     *
     * @var bool
     */
    private $isRequestCli = false;

    public function __construct()
    {
        parent::__construct(app());
    }

    /**
     * Resolves the current request context (web, control panel, CLI, etc.)
     */
    private function resolveContext()
    {
        if ($this->hasResoledContext == true) {
            return;
        }

        $currentRequest = request();

        if ($currentRequest !== null) {
            $this->isRequestControlPanel = RequestHelpers::isControlPanelRequest($currentRequest);
        }

        $this->isRequestCli = $this->app->runningInConsole();

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

        if ($this->requiresControlPanel == false &&
            $this->requiresCli == false &&
            $this->requiresWeb == false) {
            return true;
        }

        $isProviderUsable = true;

        if ($this->requiresWeb && $this->isRequestWeb == false) {
            $isProviderUsable = false;
        }

        if ($this->requiresControlPanel && $this->isRequestControlPanel == false) {
            $isProviderUsable = false;
        }

        if ($this->requiresCli && $this->isRequestCli == false) {
            $isProviderUsable = false;
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