<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorFactoryContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsManagerContract;
use Stillat\Meerkat\Core\Identity\IdentityManagerFactory;
use Stillat\Meerkat\Identity\StatamicAuthorFactory;
use Stillat\Meerkat\Identity\StatamicIdentityManager;
use Stillat\Meerkat\Permissions\StatamicAccessManager;

/**
 * Class IdentityServiceProvider
 *
 * Manages the registration of Meerkat's Identity and Authoring services.
 *
 * @since 2.0.0
 */
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
            $manager = $app->make(StatamicIdentityManager::class);

            IdentityManagerFactory::$instance = $manager;

            return $manager;
        });

        $this->app->singleton(PermissionsManagerContract::class, function ($app) {
            return $app->make(StatamicAccessManager::class);
        });
    }
}
