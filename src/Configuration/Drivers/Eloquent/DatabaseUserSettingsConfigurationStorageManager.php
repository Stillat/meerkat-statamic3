<?php

namespace Stillat\Meerkat\Configuration\Drivers\Eloquent;

use Stillat\Meerkat\Configuration\Drivers\Eloquent\Models\UserPreferences;
use Stillat\Meerkat\Contracts\Configuration\UserConfigurationStorageManagerContract;

/**
 * Class DatabaseUserSettingsConfigurationStorageManager
 *
 * Provides a user preferences storage driver targeting a database server.
 *
 * @package Stillat\Meerkat\Configuration\Drivers\Eloquent
 * @since 2.3.0
 */
class DatabaseUserSettingsConfigurationStorageManager implements UserConfigurationStorageManagerContract
{

    /**
     * Attempts to retrieve the user details for the provider user identifier.
     *
     * @param string $userId The user identifier.
     * @return array|null
     */
    public function getConfiguration($userId)
    {
        $userPreferences = UserPreferences::where('reference_user_id', $userId)->first();

        if ($userPreferences === null) {
            return null;
        }

        return json_decode($userPreferences->preferences);
    }

    /**
     * Determines if configuration exists for the provided user identifier.
     *
     * @param string $userId The user identifier.
     * @return bool
     */
    public function hasUserConfiguration($userId)
    {
        $userPreferences = UserPreferences::where('reference_user_id', $userId)->first();

        if ($userPreferences === null) {
            return false;
        }

        return true;
    }

    /**
     * Attempts to remove any existing configuration for the provided user.
     *
     * @param string $userId The user identifier.
     * @return bool
     */
    public function removeConfiguration($userId)
    {
        $deleteResult = UserPreferences::where('reference_user_id', $userId)->delete();

        if ($deleteResult === null) {
            return false;
        }

        return $deleteResult;
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
        $existingConfig = UserPreferences::where('reference_user_id', $userId)->first();

        if ($existingConfig === null) {
            $existingConfig = new UserPreferences();
            $existingConfig->reference_user_id = $userId;
            $existingConfig->preferences = json_encode($configSettings);
        } else {
            $existingConfig->preferences = json_encode($configSettings);
        }

        return $existingConfig->save();
    }

}
