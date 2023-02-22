<?php

namespace Stillat\Meerkat\Core;

/**
 * Class ConfigurationFactories
 *
 * Provides mechanisms for accessing shared configuration containers.
 *
 * @since 2.0.0
 */
class ConfigurationFactories
{
    /**
     * A shared configuration container instance.
     *
     * @var null|Configuration
     */
    public static $configurationInstance = null;

    /**
     * Indicates if the factory contains a shared Configuration instance.
     *
     * @return bool
     */
    public static function hasConfigurationInstance()
    {
        if (self::$configurationInstance === null) {
            return false;
        }

        return true;
    }
}
