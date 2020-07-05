<?php

namespace Stillat\Meerkat\Configuration;

use Illuminate\Support\Str;
use Illuminate\Foundation\Application;
use Stillat\Meerkat\Meerkat;
use Stillat\Meerkat\PathProvider;

class Manager
{

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
            $targetConfigPath = config_path(Meerkat::CODE_ADDON_NAME.'/'.$configName);

            $configMapping[$filePath] = $targetConfigPath;
        }

        return $configMapping;
    }

}