<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\Comments\StatamicCommentFactory;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Contracts\Comments\CommentFactoryContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadMutationPipelineContract;
use Stillat\Meerkat\Core\Storage\Drivers\Local\LocalCommentStorageManager;
use Stillat\Meerkat\Core\Storage\Drivers\Local\LocalThreadStorageManager;
use Stillat\Meerkat\Core\Threads\ThreadManager;
use Stillat\Meerkat\Threads\ContextResolver;
use Stillat\Meerkat\Threads\ThreadMutationPipeline;

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
    use UsesConfig;

    /**
     * The configuration key that represents the thread storage driver.
     */
    const CONFIG_THREAD_DRIVER = 'threads';

    /**
     * The configuration key that represents the comment storage driver.
     */
    const CONFIG_COMMENT_DRIVER = 'comments';

    public function register()
    {
        $driverConfiguration = $this->getConfig('storage.drivers', null);

        if ($driverConfiguration === null || is_array($driverConfiguration) === false) {
            $driverConfiguration = [];
        }

        // Provide local defaults.
        if (array_key_exists(self::CONFIG_COMMENT_DRIVER, $driverConfiguration) === false) {
            $driverConfiguration[self::CONFIG_COMMENT_DRIVER] = LocalCommentStorageManager::class;
        }

        if (array_key_exists(self::CONFIG_THREAD_DRIVER, $driverConfiguration) === false) {
            $driverConfiguration[self::CONFIG_THREAD_DRIVER] = LocalThreadStorageManager::class;
        }

        // If for some reason the specified driver does not exist, fallback to local defaults.
        if (!class_exists($driverConfiguration[self::CONFIG_COMMENT_DRIVER])) {
            $driverConfiguration[self::CONFIG_COMMENT_DRIVER] = LocalCommentStorageManager::class;
        }

        if (!class_exists($driverConfiguration[self::CONFIG_THREAD_DRIVER])) {
            $driverConfiguration[self::CONFIG_THREAD_DRIVER] = LocalThreadStorageManager::class;
        }

        $this->app->singleton(ThreadMutationPipelineContract::class, function ($app) {
            return $app->make(ThreadMutationPipeline::class);
        });

        $this->app->singleton(ContextResolverContract::class, function ($app) {
            return $app->make(ContextResolver::class);
        });

        $this->app->singleton(CommentFactoryContract::class, function ($app) {
            return $app->make(StatamicCommentFactory::class);
        });

        $this->app->singleton(CommentStorageManagerContract::class, function ($app) use ($driverConfiguration) {
            return $app->make($driverConfiguration[self::CONFIG_COMMENT_DRIVER]);
        });

        $this->app->singleton(ThreadStorageManagerContract::class, function ($app) use ($driverConfiguration) {
            return $app->make($driverConfiguration[self::CONFIG_THREAD_DRIVER]);
        });

        $this->app->singleton(ThreadManagerContract::class, function ($app) {
            return $app->make(ThreadManager::class);
        });
    }

}
