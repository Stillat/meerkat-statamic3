<?php

namespace Stillat\Meerkat\Statamic\ControlPanel;

use Stillat\Meerkat\Addon;
use Stillat\Meerkat\PathProvider;

/**
 * Class TranslationEmitter
 *
 * Provides utilities to emit JavaScript statements to patch an addon's
 * translation string key/value pairs in the Statamic Control Panel.
 *
 * @package Stillat\Meerkat\Statamic\ControlPanel
 * @since 2.0.0
 */
class TranslationEmitter
{

    /**
     * Creates a JavaScript snippet that can be utilized to patch the Statamic Control Panel translation system.
     *
     * @param $translationKeys array The translation key/value pairs to patch in the Control Panel.
     * @return string|string[]
     */
    public static function getStatements($translationKeys)
    {
        $replacements = TranslationEmitter::emitAll($translationKeys);
        $javaScriptStub = file_get_contents(PathProvider::getStub('lang.js'));

        $javaScriptStub = str_replace('@addon', Addon::ADDON_NAME . ' v' . Addon::VERSION, $javaScriptStub);
        $javaScriptStub = str_replace('/*patches*/', $replacements, $javaScriptStub);

        return $javaScriptStub;
    }

    /**
     * Emits a JavaScript statement to patch the provided translation keys.
     *
     * @param $translationKeys array The translation keys to patch.
     * @return string
     */
    public static function emitAll($translationKeys)
    {
        if (is_array($translationKeys) == false || count($translationKeys) == 0) {
            return '';
        }

        $jsString = '';

        foreach ($translationKeys as $key => $value) {
            $jsString .= TranslationEmitter::emit($key, $value);
        }

        return $jsString;
    }

    /**
     * Emits a JavaScript statement to patch the provided translation key/value pari.
     *
     * @param $transKey string The translation key.
     * @param $transValue string The translation key value.
     * @return string
     */
    public static function emit($transKey, $transValue)
    {
        $jsToEmit = '';

        if ($transValue != null && is_array($transValue) && count($transValue) > 0) {
            foreach ($transValue as $key => $value) {
                $cpKey = $transKey . '.' . $key;
                $jsValue = json_encode($value);
                $jsToEmit .= '_cst[\'' . $cpKey . '\']=JSON.parse(\'' . $jsValue . '\');';
            }
        }

        return $jsToEmit;
    }

}
