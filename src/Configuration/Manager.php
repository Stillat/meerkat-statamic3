<?php

namespace Stillat\Meerkat\Configuration;

use Illuminate\Support\Str;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\PathProvider;

/**
 * Class Manager
 *
 * Provides utilities to retrieve configuration files and mappings.
 *
 * @package Stillat\Meerkat\Configuration
 * @since 2.0.0
 */
class Manager
{

    /**
     * Gets the addon's configuration items.
     *
     * @return array
     */
    public function getConfigurationMap()
    {
        $configDirectory = PathProvider::getAddonDirectory('config');

        if (file_exists($configDirectory) == false || is_dir($configDirectory) == false) {
            return [];
        }

        if (Str::endsWith($configDirectory, '/') == false) {
            $configDirectory .= '/';
        }

        $configDirectory .= '*.php';

        $configFiles = glob($configDirectory);
        $configMapping = [];

        foreach ($configFiles as $filePath) {
            $configName = basename($filePath);
            $targetConfigPath = config_path(Addon::CODE_ADDON_NAME . '/' . $configName);

            $configMapping[$filePath] = $targetConfigPath;
        }

        return $configMapping;
    }

}
