<?php

namespace Stillat\Meerkat\Contracts\Configuration;

/**
 * Interface UserStorageManagerContract
 *
 * Represents a storage driver for manipulating user system preferences.
 *
 * @package Stillat\Meerkat\Contracts\Configuration
 * @since 2.3.0
 */
interface UserConfigurationStorageManagerContract
{

    /**
     * Attempts to retrieve the user details for the provider user identifier.
     *
     * @param string $userId The user identifier.
     * @return array|null
     */
    public function getConfiguration($userId);

    /**
     * Determines if configuration exists for the provided user identifier.
     *
     * @param string $userId The user identifier.
     * @return bool
     */
    public function hasUserConfiguration($userId);

    /**
     * Attempts to remove any existing configuration for the provided user.
     *
     * @param string $userId The user identifier.
     * @return bool
     */
    public function removeConfiguration($userId);

    /**
     * Attempts to update the configuration for the provided user.
     *
     * @param string $userId The user identifier.
     * @param array $configSettings The configuration.
     * @return bool
     */
    public function saveConfiguration($userId, $configSettings);

}
