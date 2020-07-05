<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Addon;

/**
 * Trait UsesTranslations
 *
 * Provides users with the ability to quickly resolve addon translation entries.
 *
 * @package Stillat\Meerkat\Concerns
 */
trait UsesTranslations
{


    /**
     * Translate the given message.
     *
     * @param null $key
     * @param array $replace
     * @param null $locale
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Translation\Translator|string|null
     */
    protected function trans($key = null, $replace = [], $locale = null)
    {
        $namespacedKey = Addon::CODE_ADDON_NAME.'::'.$key;

        return trans($namespacedKey, $replace, $locale);
    }

    /**
     * Translate the given message based on a count.
     *
     * @param $key
     * @param $number
     * @param array $replace
     * @param null $locale
     * @return string
     */
    protected function transChoice($key, $number, array $replace = [], $locale = null)
    {
        $namespacedKey = Addon::CODE_ADDON_NAME.'::'.$key;

        return trans_choice($namespacedKey, $number, $replace, $locale);
    }

}