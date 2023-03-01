<?php

namespace Stillat\Meerkat\Core;

/**
 * Class ConfigurationContainer
 *
 * A generalized configuration container implementation
 *
 * Similar to the data object, this configuration container allows
 * users to store/retrieve arbitrary configuration data items.
 *
 * @since 2.0.0
 */
class ConfigurationContainer
{
    /**
     * Configuration entries for extra items.
     *
     * @var array
     */
    private $otherConfiguration = [];

    /**
     * Returns the configuration value for the provided key, or the default.
     *
     * @param  string  $key The configuration key to lookup.
     * @param  object  $default The default value to return if no configuration key is found.
     * @return string|object
     */
    public function get($key, $default = null)
    {
        if (! $this->has($key)) {
            return $default;
        }

        return $this->otherConfiguration[$key];
    }

    /**
     * Returns a value indicating if the provided configuration key exists.
     *
     * @param  string  $key The configuration key to lookup.
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->otherConfiguration);
    }

    /**
     * Sets the configuration value for the provided key.
     *
     * @param  string  $key The configuration key to set.
     * @param  string|object  $value The configuration value to set for the key.
     * @return void
     */
    public function set($key, $value)
    {
        $this->otherConfiguration[$key] = $value;
    }
}
