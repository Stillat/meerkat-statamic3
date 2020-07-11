<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorFactoryContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Identity\StatamicAuthorFactory;
use Stillat\Meerkat\Identity\StatamicIdentityManager;

class IdentityServiceProvider extends AddonServiceProvider
{

    /**
     * Provides bindings for the Meerkat Core Identity services.
     */
    public function register()
    {
        $this->app->singleton(AuthorFactoryContract::class, function ($app) {
            return $app->make(StatamicAuthorFactory::class);
        });

        $this->app->singleton(IdentityManagerContract::class, function ($app) {
            return $app->make(StatamicIdentityManager::class);
        });
    }

}
