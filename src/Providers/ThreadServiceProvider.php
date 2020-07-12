<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Threads\ContextResolver;

/**
 * Class ThreadServiceProvider
 *
 * Registers the thread and comment Meerkat Core services.
 *
 * @package Stillat\Meerkat\Providers
 * @since 2.0.0
 */
class ThreadServiceProvider extends AddonServiceProvider
{

    public function register()
    {
        $this->app->singleton(ContextResolverContract::class, function ($app) {
            return $app->make(ContextResolver::class);
        });
    }

}
