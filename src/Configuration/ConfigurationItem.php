<?php

namespace Stillat\Meerkat\Configuration;

use Stillat\Meerkat\Core\Support\Arr;

/**
 * Class ConfigurationItem
 *
 * Represents an individual configuration item.
 *
 * @since 2.1.0
 */
class ConfigurationItem
{
    const KEY_NAMESPACE = 'namespace';

    const KEY_KEY = 'key';

    const KEY_BEHAVIOR = 'behavior';

    const KEY_DEFAULTS = 'defaults';

    const KEY_VALUE = 'value';

    /**
     * The configuration item's namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * The configuration item's key.
     *
     * @var string
     */
    protected $key = '';

    /**
     * The configured override behavior.
     *
     * @var int
     */
    protected $configBehavior = Manager::BEHAVIOR_MANAGED;

    /**
     * The default values that cannot be removed, if a list type.
     *
     * @var array
     */
    protected $defaultValues = [];

    /**
     * The current value.
     *
     * @var null|mixed
     */
    protected $value = null;

    /**
     * Gets the configuration item's namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Sets the configuration item's namespace.
     *
     * @param  string  $namespace The namespace.
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Gets the configuration item's key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Sets the configuration item's key.
     *
     * @param  string  $key The key.
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Sets the configuration item's override behavior.
     *
     * @param  int  $behavior The override behavior.
     */
    public function setBehavior($behavior)
    {
        $this->configBehavior = $behavior;
    }

    /**
     * Gets the configuration item's override behavior.
     *
     * @return int
     */
    public function getBehavior()
    {
        return $this->configBehavior;
    }

    /**
     * Gets the configuration item's current value.
     *
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the configuration item's current value.
     *
     * @param  mixed  $value The value.
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Gets the configuration item's default values, if a list type.
     *
     * @return array
     */
    public function getDefaultValues()
    {
        return $this->defaultValues;
    }

    /**
     * Sets the configuration item's default values, if a list type.
     *
     * @param  array  $values The default values.
     */
    public function setDefaultValues($values)
    {
        $this->defaultValues = $values;
    }

    /**
     * Converts the configuration item to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::KEY_NAMESPACE => $this->namespace,
            self::KEY_KEY => $this->key,
            self::KEY_BEHAVIOR => $this->configBehavior,
            self::KEY_DEFAULTS => $this->defaultValues,
            self::KEY_VALUE => $this->value,
        ];
    }

    /**
     * Attempts to construct a ConfigurationItem instance from the array data.
     *
     * @param  array  $array The item data.
     * @return ConfigurationItem
     */
    public static function fromArray($array)
    {
        $configItem = new ConfigurationItem();

        if (Arr::matches([
            self::KEY_NAMESPACE, self::KEY_KEY,
            self::KEY_BEHAVIOR, self::KEY_DEFAULTS, self::KEY_VALUE,
        ], $array)) {
            $configItem->setNamespace($array[self::KEY_NAMESPACE]);
            $configItem->setKey($array[self::KEY_KEY]);
            $configItem->setBehavior($array[self::KEY_BEHAVIOR]);
            $configItem->setDefaultValues($array[self::KEY_DEFAULTS]);

            $value = $array[self::KEY_VALUE];

            if (is_array($value)) {
                $cleanedValues = [];

                foreach ($value as $item) {
                    if (! is_string($item)) {
                        continue;
                    }

                    $cleanedValues[] = $item;
                }

                $value = array_values(array_unique($cleanedValues));
            }

            $configItem->setValue($value);
        }

        return $configItem;
    }
}
