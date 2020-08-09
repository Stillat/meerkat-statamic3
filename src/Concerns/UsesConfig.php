<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Addon;

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

}
