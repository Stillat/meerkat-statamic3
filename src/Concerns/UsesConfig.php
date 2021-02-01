<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Configuration\VendorConfigurationCache;

/**
 * Trait UsesConfig
 *
 * Provides users with the ability to quickly resolve addon configuration entries.
 *
 * @package Stillat\Meerkat\Concerns
 * @since 2.0.0
 */
trait UsesConfig
{

    /**
     * Gets an addon configuration entry.
     *
     * @param string $key The configuration key.
     * @param null|mixed $default The default value to return
     *
     * @return mixed
     */
    protected function getConfig($key, $default = null)
    {
        // Create a namespaced configuration key using "dot" notation.
        $namespacedKey = Addon::CODE_ADDON_NAME . '.' . $key;

        return config($namespacedKey, $default);
    }

    /**
     * Retrieves a configuration value from the vendor Meerkat configuration files, or a default.
     *
     * @param string $key The configuration key.
     * @param null|mixed $default The default value.
     * @return array|mixed|null
     */
    protected function getVendorConfig($key, $default = null)
    {
        $configurationParts = explode('.', $key);

        if (count($configurationParts) <= 1) {
            return $default;
        }

        $configurationFileName = array_shift($configurationParts);
        $configurationKey = implode('.', $configurationParts);

        return VendorConfigurationCache::getConfiguration($configurationFileName, $configurationKey, $default);
    }

    /**
     * Helper utility to resolve driver configuration from manually specified values, or from storage driver groups.
     *
     * @return array
     */
    protected function getStorageDriverConfiguration()
    {
        $driverConfiguration = $this->getConfig('storage.drivers', null);

        if ($driverConfiguration !== null && !is_array($driverConfiguration) && is_string($driverConfiguration)) {
            // The getVendorConfig call ensures that we still resolve the correct values, even if
            // site developers have not updated their site's local configuration files to match.
            $defaultGroups = $this->getConfig('storage.storage_drivers', $this->getVendorConfig('storage.storage_drivers', []));

            if (array_key_exists($driverConfiguration, $defaultGroups) && is_array($defaultGroups[$driverConfiguration])) {
                $driverConfiguration = $defaultGroups[$driverConfiguration];
            }
        }

        if ($driverConfiguration === null || is_array($driverConfiguration) === false) {
            return [];
        }

        return $driverConfiguration;
    }

}
