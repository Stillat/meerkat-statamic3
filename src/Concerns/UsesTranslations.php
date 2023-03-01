<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Addon;

/**
 * Trait UsesTranslations
 *
 * Provides users with the ability to quickly resolve addon translation entries.
 *
 * @since 2.0.0
 */
trait UsesTranslations
{
    /**
     * Translate the given message.
     *
     * @param  null|string  $key The translation key.
     * @param  array  $replace Translation replacements.
     * @param  null|string  $locale The locale to utilize.
     * @return string|null
     */
    protected function trans($key = null, $replace = [], $locale = null)
    {
        $namespacedKey = Addon::CODE_ADDON_NAME.'::'.$key;

        return trans($namespacedKey, $replace, $locale);
    }

    protected function translateErrorCode($errorCode, $result)
    {
        $errorKey = $this->trans('codes.'.$errorCode);

        return $this->trans($errorKey, $result);
    }

    /**
     * Translate the given message based on a count.
     *
     * @param  string  $key The translation key.
     * @param  int  $number The number of items to consider when translating.
     * @param  array  $replace The translation replacements.
     * @param  null|string  $locale The locale to utilize.
     * @return string
     */
    protected function transChoice($key, $number, array $replace = [], $locale = null)
    {
        $namespacedKey = Addon::CODE_ADDON_NAME.'::'.$key;

        return trans_choice($namespacedKey, $number, $replace, $locale);
    }
}
