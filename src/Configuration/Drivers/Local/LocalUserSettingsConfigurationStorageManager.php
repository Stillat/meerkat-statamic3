<?php

namespace Stillat\Meerkat\Configuration\Drivers\Local;

use Statamic\Facades\YAML;
use Stillat\Meerkat\Contracts\Configuration\UserConfigurationStorageManagerContract;

/**
 * Class LocalUserSettingsConfigurationStorageManager
 *
 * Provides a user preferences storage driver implementation targeting the local filesystem.
 *
 * @package Stillat\Meerkat\Configuration\Drivers\Local
 * @since 2.3.0
 */
class LocalUserSettingsConfigurationStorageManager implements UserConfigurationStorageManagerContract
{

    /**
     * Determines if configuration exists for the provided user identifier.
     *
     * @param string $userId The user identifier.
     * @return bool
     */
    public function hasUserConfiguration($userId)
    {
        return file_exists($this->getLocalUserStoragePath($userId));
    }

    /**
     * Attempts to retrieve the user details for the provider user identifier.
     *
     * @param string $userId The user identifier.
     * @return array|null
     */
    public function getConfiguration($userId)
    {
        $storagePath = $this->getLocalUserStoragePath($userId);

        if ($this->hasUserConfiguration($userId) === false) {
            return null;
        }

        $contents = file_get_contents($storagePath);

        return YAML::parse($contents);
    }

    /**
     * Attempts to remove any existing configuration for the provided user.
     *
     * @param string $userId The user identifier.
     * @return bool
     */
    public function removeConfiguration($userId)
    {
        if ($this->hasUserConfiguration($userId) === false) {
            return false;
        }

        $results = unlink($this->getLocalUserStoragePath($userId));

        if ($results === false) {
            return false;
        }

        return true;
    }

    /**
     * Attempts to update the configuration for the provided user.
     *
     * @param string $userId The user identifier.
     * @param array $configSettings The configuration.
     * @return bool
     */
    public function saveConfiguration($userId, $configSettings)
    {
        $storagePath = $this->getLocalUserStoragePath($userId);
        $settingContent = YAML::dump($configSettings);

        $results = file_put_contents($storagePath, $settingContent);

        if ($results === false) {
            return false;
        }

        return true;
    }

    /**
     * Returns the local file path for the provided user's Control Panel configuration.
     *
     * @param string $userId The user identifier.
     * @return string
     */
    private function getLocalUserStoragePath($userId)
    {
        return config_path('meerkat/users/' . $userId . '.yaml');
    }

}
