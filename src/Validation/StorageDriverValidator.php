<?php

namespace Stillat\Meerkat\Validation;

use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Storage\Drivers\Local\LocalCommentStorageManager;
use Stillat\Meerkat\Core\Storage\Drivers\Local\LocalThreadStorageManager;
use Stillat\Meerkat\Core\Threads\Thread;
use Stillat\Meerkat\Core\ValidationResult;
use Stillat\Meerkat\PathProvider;
use Stillat\Meerkat\Providers\ThreadServiceProvider;

/**
 * Class StorageDriverValidator
 *
 * Provides utilities for validating the Meerkat storage drivers.
 *
 * @package Stillat\Meerkat\Validation
 * @since 2.0.0
 */
class StorageDriverValidator
{
    use UsesConfig;

    /**
     * Validates the configured Meerkat storage drivers.
     *
     * @return ValidationResult
     */
    public function validate()
    {
        $validationResults = new ValidationResult();

        $driverConfiguration = $this->getConfig('storage.drivers', null);

        // Guard against unexpected values.
        if ($driverConfiguration === null || is_array($driverConfiguration) === false) {
            $validationResults->add(Errors::DRIVER_CONFIGURATION_NOT_FOUND, 'No storage drivers were configured. Local drivers will be utilized.');
            $driverConfiguration = [];
        }

        // Provide local defaults.
        if (array_key_exists(ThreadServiceProvider::CONFIG_COMMENT_DRIVER, $driverConfiguration) === false) {
            $validationResults->add(Errors::DRIVER_COMMENT_NONE_SUPPLIED, 'Comment storage driver not found. Using fallback: '.LocalCommentStorageManager::class);
            $driverConfiguration[ThreadServiceProvider::CONFIG_COMMENT_DRIVER] = LocalCommentStorageManager::class;
        }

        if (array_key_exists(ThreadServiceProvider::CONFIG_THREAD_DRIVER, $driverConfiguration) === false) {
            $driverConfiguration[self::CONFIG_THREAD_DRIVER] = LocalThreadStorageManager::class;
            $validationResults->add(Errors::DRIVER_THREAD_NONE_SUPPLIED, 'Thread storage driver not found. Using fallback: '.LocalThreadStorageManager::class);
            $driverConfiguration[ThreadServiceProvider::CONFIG_THREAD_DRIVER] = LocalThreadStorageManager::class;
        }

        // If for some reason the specified driver does not exist, fallback to local defaults.
        if (!class_exists($driverConfiguration[ThreadServiceProvider::CONFIG_COMMENT_DRIVER])) {
            $validationResults->add(Errors::DRIVER_COMMENT_PROVIDED_NOT_FOUND, 'The provided comment storage driver ('.$driverConfiguration[ThreadServiceProvider::CONFIG_COMMENT_DRIVER].') could not be found. Using fallback: '.LocalCommentStorageManager::class);
            $driverConfiguration[ThreadServiceProvider::CONFIG_COMMENT_DRIVER] = LocalCommentStorageManager::class;
        }

        if (!class_exists($driverConfiguration[ThreadServiceProvider::CONFIG_THREAD_DRIVER])) {
            $validationResults->add(Errors::DRIVER_THREAD_PROVIDED_NOT_FOUND, 'The provided thread storage driver ('.$driverConfiguration[ThreadServiceProvider::CONFIG_THREAD_DRIVER].') could not be found. Using fallback: '.LocalThreadStorageManager::class);
            $driverConfiguration[ThreadServiceProvider::CONFIG_THREAD_DRIVER] = LocalThreadStorageManager::class;
        }

        $validationResults->updateValidity();

        $validationResults->setDataAttribute('driver_configuration', $driverConfiguration);
        $validationResults->setDataAttribute('configured_storage_path', $this->getConfig('storage.path'));
        $validationResults->setDataAttribute('using_storage_path', PathProvider::contentPath());


        return $validationResults;
    }

}
