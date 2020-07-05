<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Addon;

/**
 * Trait UsesConfig
 *
 * Provides users with the ability to quickly resolve addon configuration entries.
 *
 * @package Stillat\Meerkat\Concerns
 */
trait UsesConfig
{

    /**
     * Gets an addon configuration entry.
     *
     * @param $key
     * @param null $default
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected function getConfig($key, $default = null)
    {
        // Create a namespaced configuration key using "dot" notation.
        $namespacedKey = Addon::CODE_ADDON_NAME.'.'.$key;

        return config($namespacedKey, $default);
    }

}