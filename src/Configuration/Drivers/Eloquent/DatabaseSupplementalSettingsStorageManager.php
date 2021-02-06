<?php

namespace Stillat\Meerkat\Configuration\Drivers\Eloquent;

use Stillat\Meerkat\Configuration\Drivers\Eloquent\Models\SupplementalPreferences;
use Stillat\Meerkat\Contracts\Configuration\SupplementalStorageManagerContract;

/**
 * Class DatabaseSupplementalSettingsStorageManager
 *
 * Provides a supplemental storage driver targeting a database server.
 *
 * @package Stillat\Meerkat\Configuration\Drivers\Eloquent
 * @since 2.3.0
 */
class DatabaseSupplementalSettingsStorageManager implements SupplementalStorageManagerContract
{

    /**
     * Attempts to save the provided configuration.
     *
     * @param array $configSettings The configuration.
     * @return bool
     */
    public function saveConfiguration($configSettings)
    {
        $existingConfiguration = $this->getUsableConfigurationEntry();

        if ($existingConfiguration === null) {
            $existingConfiguration = new SupplementalPreferences();
            $existingConfiguration->preferences = json_encode($configSettings);
        } else {
            $existingConfiguration->preferences = json_encode($configSettings);
        }

        return $existingConfiguration->save();
    }

    /**
     * Returns the first non-archived SupplementalPreferences record instance.
     *
     * @return SupplementalPreferences|null
     */
    private function getUsableConfigurationEntry()
    {
        return SupplementalPreferences::whereNull('deleted_at')->first();
    }

    /**
     * Retrieves all requested namespaces that can be managed.
     *
     * @param array $namespaces The configuration namespaces to retrieve.
     * @param array $managedNamespaces A list of all managed namespaces.
     * @return array
     */
    public function getAllSupplementalConfiguration($namespaces, $managedNamespaces)
    {
        $existingConfiguration = $this->getUsableConfigurationEntry();

        if ($existingConfiguration === null) {
            return [];
        }

        return json_decode($existingConfiguration->preferences, true);
    }

    /**
     * Retrieves a hash value, representing the persisted configuration state.
     *
     * @param array $namespaces The configuration namespaces to analyze.
     * @param array $managedNamespaces A list of all managed namespaces.
     * @return string
     */
    public function getUpdateHash($namespaces, $managedNamespaces)
    {
        $existingConfiguration = $this->getUsableConfigurationEntry();

        $valueToHash = '';

        if ($existingConfiguration !== null) {
            $valueToHash = 'h'. strval($existingConfiguration->updated_at->timestamp);
        }

        return md5($valueToHash);
    }

}