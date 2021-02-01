<?php

namespace Stillat\Meerkat\Configuration;

use Illuminate\Support\Arr;
use Stillat\Meerkat\PathProvider;

/**
 * Class VendorConfigurationCache
 *
 * Provides access to the vendor Meerkat configuration values.
 *
 * @package Stillat\Meerkat\Configuration
 * @since 2.3.0
 */
class VendorConfigurationCache
{

    /**
     * A cache of all resolved vendor configuration values.
     *
     * @var array
     */
    public static $configCache = [];

    /**
     * Retrieves a default configuration value, or a default value.
     *
     * @param string $configFile The configuration file name, or namespace.
     * @param string $configKey The configuration key.
     * @param mixed|null $default The default value.
     * @return array|mixed|null
     */
    public static function getConfiguration($configFile, $configKey, $default = null)
    {
        $vendorConfigurationPath = PathProvider::getConfigurationPath($configFile.'.php');

        if (file_exists($vendorConfigurationPath) === false) {
            return $default;
        }

        if (array_key_exists($configKey, self::$configCache) === false) {
            self::$configCache[$configFile] = require $vendorConfigurationPath;
        }

        return Arr::get(self::$configCache[$configFile], $configKey, $default);
    }

}
