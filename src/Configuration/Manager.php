<?php

namespace Stillat\Meerkat\Configuration;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Statamic\Facades\YAML;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Data\Types;
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
    use UsesConfig;

    const PATH_SUPPLEMENT = 'supplement';
    const CONFIG_OVERRIDE_SUFFIX = '_behavior';

    const BEHAVIOR_MANAGED = 0;
    const BEHAVIOR_MERGE = 1;
    const BEHAVIOR_REPLACE = 2;
    const BEHAVIOR_USER_VALUE_OR_REPLACE = 3;

    /**
     * The shared configuration Manager instance, if available.f
     *
     * @var Manager|null
     */
    public static $instance = null;

    /**
     * All of the runtime configuration values.
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * The configuration items.
     *
     * @var ConfigurationItem[]
     */
    protected $configItems = [];

    /**
     * Indicates if there are any managed configuration values.
     *
     * @var bool
     */
    protected $hasManagedItems = true;

    /**
     * Indicates if the configuration has already been resolved.
     *
     * @var bool
     */
    protected $hasLoaded = false;

    /**
     * A list of configuration namespaces that are always managed, and cannot be changed in the Control Panel.
     *
     * @var string[]
     */
    protected $alwaysManaged = [
        'telemetry', 'storage'
    ];

    /**
     * A list of configuration values that should not be returned in API responses.
     *
     * @var string[]
     */
    protected $notReturnable = [
        'authors.form_user_fields', 'publishing.honeypot', 'akismet.fields', 'permissions.control_panel_config'
    ];

    /**
     * The default behaviors of various configuration items.
     *
     * @var array[]
     */
    protected $behaviorDefaults = [
        'akismet.api_key' => [
            self::BEHAVIOR_MANAGED,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_STRING
        ],

        'akismet.front_page' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_STRING
        ],

        'email.send_mail' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_BIT
        ],

        'email.addresses' => [
            self::BEHAVIOR_MERGE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'email.check_with_spam_guard' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_BIT
        ],

        'authors.cp_avatar_driver' => [
            self::BEHAVIOR_USER_VALUE_OR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE, self::BEHAVIOR_USER_VALUE_OR_REPLACE],
            Types::TYPE_STRING
        ],

        'iplist.block' => [
            self::BEHAVIOR_MERGE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'wordlist.banned' => [
            self::BEHAVIOR_MERGE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'permissions.control_panel_config' => [
            self::BEHAVIOR_MANAGED,
            [self::BEHAVIOR_MANAGED],
            Types::TYPE_BIT
        ],

        'permissions.all_permissions' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'permissions.can_view_comments' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'permissions.can_approve_comments' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'permissions.can_unapprove_comments' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'permissions.can_reply_to_comments' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'permissions.can_edit_comments' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'permissions.can_report_as_spam' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'permissions.can_report_as_ham' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'permissions.can_remove_comments' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'publishing.guards' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

        'publishing.auto_publish' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_BIT
        ],

        'publishing.auto_publish_authenticated_users' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_BIT
        ],

        'publishing.automatically_close_comments' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_INTEGER
        ],

        'publishing.guard_check_all_providers' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_BIT
        ],

        'publishing.guard_unpublish_on_guard_failure' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_BIT
        ],

        'publishing.auto_check_spam' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_BIT
        ],

        'publishing.auto_delete_spam' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_BIT
        ],

        'publishing.auto_submit_results' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_BIT
        ],

        'publishing.honeypot' => [
            self::BEHAVIOR_REPLACE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_REPLACE],
            Types::TYPE_STRING
        ],

        'search.attributes' => [
            self::BEHAVIOR_MERGE,
            [self::BEHAVIOR_MANAGED, self::BEHAVIOR_MERGE, self::BEHAVIOR_REPLACE],
            Types::TYPE_LIST
        ],

    ];
    /**
     * A collection of valid user groups.
     *
     * @var string[]
     */
    protected $userGroups = null;
    /**
     * A cache of all the observed configuration behaviors.
     *
     * @var array
     */
    private $behaviorCache = [];
    /**
     * Indicates if Control Panel configuration was enabled.
     *
     * @var bool
     */
    private $cpConfigEnabled = false;

    public function __construct()
    {
        $this->cpConfigEnabled = $this->getConfig('permissions.control_panel_config', true);
        $this->userGroups = null; //$userGroups;
    }

    protected function getConfig($key, $default = null)
    {
        // Create a namespaced configuration key using "dot" notation.
        $namespacedKey = Addon::CODE_ADDON_NAME . '.' . $key;

        return config($namespacedKey, $default);
    }

    /**
     * Determines if the shared configuration Manager was set.
     *
     * @return bool
     */
    public static function hasInstance()
    {
        if (self::$instance === null) {
            return false;
        }

        return true;
    }

    /**
     * Sets the collection of valid user groups.
     *
     * @param string[] $groups The valid user groups.
     */
    public function setValidGroups($groups)
    {
        $this->userGroups = $groups;
    }

    /**
     * Gets the current supplemental configuration set hash.
     *
     * @return string
     */
    public function getConfigurationHash()
    {
        $configNamespaces = array_map(function ($v) {
            return pathinfo($v, PATHINFO_FILENAME);
        }, array_values($this->getConfigurationMap()));

        $valueToHash = '';

        foreach ($configNamespaces as $namespace) {
            $allConfigValues[$namespace] = $this->getConfig($namespace);

            $supplementalConfigPath = $this->getSupplementPath($namespace);

            if (!in_array($namespace, $this->alwaysManaged) &&
                file_exists($supplementalConfigPath) && is_readable($supplementalConfigPath)) {
                $valueToHash .= 'h' . filemtime($supplementalConfigPath);
            }
        }

        return md5($valueToHash);
    }

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

    private function getSupplementPath($namespace)
    {
        $configDirectory = config_path(Addon::CODE_ADDON_NAME . '/');
        if (Str::endsWith($configDirectory, '/') == false) {
            $configDirectory .= '/';
        }

        return $configDirectory . self::PATH_SUPPLEMENT . '/' . $namespace . '.yaml';
    }

    /**
     * @return ConfigurationItem[]
     */
    public function getConfigurationItems()
    {
        return $this->configItems;
    }

    /**
     * Indicates if any values are managed by a system administrator.
     *
     * @return bool
     */
    public function hasManagedItems()
    {
        return $this->hasManagedItems;
    }

    /**
     * Converts the current configuration items to arrays.
     *
     * @return array
     */
    public function getConfigurationItemArray()
    {
        $items = [];

        foreach ($this->configItems as $item) {
            $items[] = $item->toArray();
        }

        return $items;
    }

    /**
     * Attempts to save the provided items into supplemental storage.
     *
     * @param ConfigurationItem[] $items The configuration items.
     * @return bool
     */
    public function save($items)
    {
        if ($this->cpConfigEnabled === false) {
            return false;
        }

        $itemsToSave = [];
        // Check against any configuration property that was marked as managed only.
        foreach ($items as $item) {
            $namespaceKey = $item->getNamespace() . '.' . $item->getKey();
            if (array_key_exists($namespaceKey, $this->behaviorCache) && $this->behaviorCache[$namespaceKey] !== self::BEHAVIOR_MANAGED) {
                $itemsToSave[] = $item;
            }
        }

        unset($items);

        $itemsToSave = $this->unwrapConfigurationItems($itemsToSave);

        if (array_key_exists('permissions', $itemsToSave)) {
            foreach ($itemsToSave['permissions'] as $permNamespace => $perm) {
                $validatedPerms = array_intersect($this->userGroups, $perm);
                $itemsToSave['permissions'][$permNamespace] = array_values(array_unique($validatedPerms));
            }
        }

        $results = [];

        foreach ($itemsToSave as $namespace => $config) {
            $path = $this->getSupplementalConfigurationPath($namespace);
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
     * Converts the list of configuration items to a structured list.
     *
     * @param ConfigurationItem[] $items The configuration items to normalize.
     * @return array
     */
    private function unwrapConfigurationItems($items)
    {
        $returnItems = [];

        foreach ($items as $item) {
            if (array_key_exists($item->getNamespace(), $returnItems) === false) {
                $returnItems[$item->getNamespace()] = [];
            }

            $returnItems[$item->getNamespace()][$item->getKey()] = $item->getValue();
        }

        unset($items);

        return $returnItems;
    }

    /**
     * Converts the provided configuration sub-namespace to a storage path.
     *
     * @param string $namespace The config namespace.
     * @return string
     */
    private function getSupplementalConfigurationPath($namespace)
    {
        return config_path('meerkat/supplement/' . $namespace . '.yaml');
    }

    /**
     * Reloads all dynamic configuration values.
     */
    public function loadConfiguration()
    {
        if ($this->hasLoaded === true) {
            return;
        }

        $this->hasLoaded = true;
        $this->configItems = [];
        $this->configuration = [];

        $configNamespaces = array_map(function ($v) {
            return pathinfo($v, PATHINFO_FILENAME);
        }, array_values($this->getConfigurationMap()));

        $allConfigValues = [];
        $supplementalConfiguration = [];

        foreach ($configNamespaces as $namespace) {
            $allConfigValues[$namespace] = $this->getConfig($namespace);

            $supplementalConfigPath = $this->getSupplementPath($namespace);

            if (!in_array($namespace, $this->alwaysManaged) &&
                file_exists($supplementalConfigPath) && is_readable($supplementalConfigPath)) {
                $contents = file_get_contents($supplementalConfigPath);

                if (mb_strlen($contents) > 0) {
                    $supplementalConfiguration[$namespace] = YAML::parse($contents);
                }
            }
        }

        foreach ($allConfigValues as $namespace => $configValues) {

            if (in_array($namespace, $this->alwaysManaged)) {
                continue;
            }

            if (!is_array($configValues)) {
                continue;
            }

            $supplements = [];

            if (array_key_exists($namespace, $supplementalConfiguration)) {
                $supplements = $supplementalConfiguration[$namespace];
            }

            foreach ($configValues as $configKey => $configValue) {
                if (Str::endsWith($configKey, self::CONFIG_OVERRIDE_SUFFIX)) {
                    continue;
                }

                $configEntry = $namespace . '.' . $configKey;
                $configValue = $this->getConfig($configEntry);
                $valueToReturn = $configValue;
                $defaultValues = [];

                if (!array_key_exists($configEntry, $this->behaviorDefaults)) {
                    continue;
                }

                $expectedDefaults = $this->behaviorDefaults[$configEntry];

                $overrideKey = $configKey . self::CONFIG_OVERRIDE_SUFFIX;
                $overrideValue = Arr::get($allConfigValues[$namespace], $overrideKey, $expectedDefaults[0]);
                $expectedType = $expectedDefaults[2];

                if (!in_array($overrideValue, $expectedDefaults[1])) {
                    $overrideValue = $expectedDefaults[0];
                }

                $this->behaviorCache[$configEntry] = $overrideValue;
                $runtimeConfigKey = Addon::CODE_ADDON_NAME . '.' . $configEntry;

                if ($overrideValue === self::BEHAVIOR_REPLACE) {
                    if (array_key_exists($configKey, $supplements)) {
                        $configValue = $supplements[$configKey];
                        $valueToReturn = $configValue;
                    }

                    if ($this->cpConfigEnabled === true && $this->shouldUseValue($expectedType, $configValue)) {
                        Config::set($runtimeConfigKey, $configValue);
                    }

                } elseif ($overrideValue === self::BEHAVIOR_MERGE) {
                    if (array_key_exists($configKey, $supplements)) {
                        $supplementValue = $supplements[$configKey];

                        if (is_array($supplementValue)) {
                            $valuesToMerge = array_values($supplementValue);

                            $defaultValues = $configValue;
                            $valueToReturn = $valuesToMerge;
                            $configValue = array_merge($configValue, $valuesToMerge);

                            if ($this->cpConfigEnabled === true && $this->shouldUseValue($expectedType, $configValue)) {
                                Config::set($runtimeConfigKey, $configValue);
                            }
                        }
                    } else {
                        $valueToReturn = [];
                        $defaultValues = $configValue;
                    }
                }

                if (!in_array($configEntry, $this->notReturnable)) {
                    $configItem = new ConfigurationItem();
                    $configItem->setNamespace($namespace);
                    $configItem->setBehavior($overrideValue);
                    $configItem->setKey($configKey);
                    $configItem->setKey($configKey);
                    $configItem->setValue($valueToReturn);
                    $configItem->setDefaultValues($defaultValues);

                    $this->configItems[] = $configItem;
                }
            }
        }

        foreach ($configNamespaces as $namespace) {
            $this->configuration[$namespace] = $this->getConfig($namespace);
        }
    }

    /**
     * Checks if the supplemental configuration value should be used.
     *
     * @param int $expectedType The expected data type.
     * @param mixed $currentValue The current supplement value.
     * @return bool
     */
    private function shouldUseValue($expectedType, $currentValue)
    {
        if ($expectedType === Types::TYPE_STRING && is_string($currentValue)) {
            return true;
        } elseif ($expectedType === Types::TYPE_BIT) {
            if (is_bool($currentValue)) {
                return true;
            }

            if (is_string($currentValue) && in_array($currentValue, ['true', ['false']])) {
                return true;
            }
        } elseif ($expectedType === Types::TYPE_LIST && is_array($currentValue)) {
            return true;
        } elseif ($expectedType === Types::TYPE_INTEGER && is_int($currentValue) || intval($currentValue) == $currentValue) {
            return true;
        }

        return false;
    }

    /**
     * Returns a mapping between configuration namespaces and supplemental storage paths.
     *
     * @return array
     */
    public function getSupplementalConfigurationMap()
    {
        $configurationMap = $this->getConfigurationMap();
        $supplementalMap = [];

        foreach ($configurationMap as $source => $target) {
            $filePath = pathinfo($target, PATHINFO_DIRNAME) . '/' . self::PATH_SUPPLEMENT . '/';
            $fileName = pathinfo($target, PATHINFO_FILENAME);

            if (in_array($fileName, $this->alwaysManaged)) {
                continue;
            }

            $supplementalPath = $filePath . $fileName . '.yaml';

            $supplementalMap[] = $supplementalPath;
        }

        return $supplementalMap;
    }

}
