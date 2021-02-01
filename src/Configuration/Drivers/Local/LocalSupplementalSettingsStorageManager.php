<?php

namespace Stillat\Meerkat\Configuration\Drivers\Local;

use Illuminate\Support\Str;
use Statamic\Facades\YAML;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Contracts\Configuration\SupplementalStorageManagerContract;

/**
 * Class LocalSupplementalSettingsStorageManager
 *
 * Provides interaction between the local file-system and Meerkat's configuration manager systems.
 *
 * @package Stillat\Meerkat\Configuration\Drivers\Local
 * @since 2.3.0
 */
class LocalSupplementalSettingsStorageManager implements SupplementalStorageManagerContract
{
    const PATH_SUPPLEMENT = 'supplement';

    /**
     * Attempts to save the provided configuration.
     *
     * @param array $configSettings The configuration.
     * @return bool
     */
    public function saveConfiguration($configSettings)
    {
        $results = [];

        foreach ($configSettings as $namespace => $config) {
            $path = $this->getSupplementPath($namespace);
            $configContents = YAML::dump($config);

            $results[$path] = file_put_contents($path, $configContents);
        }

        foreach ($results as $result) {
            if ($result === false) {
                return false;
            }
        }

        return true;
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
        $supplementalConfiguration = [];

        foreach ($namespaces as $namespace) {
            $allConfigValues[$namespace] = $this->getConfig($namespace);

            $supplementalConfigPath = $this->getSupplementPath($namespace);

            if (!in_array($namespace, $managedNamespaces) &&
                file_exists($supplementalConfigPath) && is_readable($supplementalConfigPath)) {
                $contents = file_get_contents($supplementalConfigPath);

                if (mb_strlen($contents) > 0) {
                    $supplementalConfiguration[$namespace] = YAML::parse($contents);
                }
            }
        }

        return $supplementalConfiguration;
    }

    /**
     * Retrieves the current run-time value for the provided configuration key.
     *
     * @param string $key The configuration key.
     * @param mixed|null $default The default value.
     * @return mixed
     */
    private function getConfig($key, $default = null)
    {
        // Create a namespaced configuration key using "dot" notation.
        $namespacedKey = Addon::CODE_ADDON_NAME . '.' . $key;

        return config($namespacedKey, $default);
    }

    /**
     * Generates a supplemental configuration path for the provided configuration namespace.
     *
     * @param string $namespace The configuration namespace.
     * @return string
     */
    private function getSupplementPath($namespace)
    {
        $configDirectory = config_path(Addon::CODE_ADDON_NAME . '/');
        if (Str::endsWith($configDirectory, '/') == false) {
            $configDirectory .= '/';
        }

        return $configDirectory . self::PATH_SUPPLEMENT . '/' . $namespace . '.yaml';
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
        $valueToHash = '';

        foreach ($namespaces as $namespace) {
            $allConfigValues[$namespace] = $this->getConfig($namespace);

            $supplementalConfigPath = $this->getSupplementPath($namespace);

            if (!in_array($namespace, $managedNamespaces) &&
                file_exists($supplementalConfigPath) && is_readable($supplementalConfigPath)) {
                $valueToHash .= 'h' . filemtime($supplementalConfigPath);
            }
        }

        return md5($valueToHash);
    }

}
