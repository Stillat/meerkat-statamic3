<?php

namespace Stillat\Meerkat\Translation;

use Illuminate\Translation\Translator;
use Statamic\Facades\Folder;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\PathProvider;

/**
 * Class LanguagePatcher
 *
 * Utility to find any language key that returns the key in the current locale. When
 * this happens, a translation string resolved to the fallback locale is used.
 *
 * @package Stillat\Meerkat\Translation
 */
class LanguagePatcher
{

    /**
     * The translator instance.
     *
     * @var Translator|null
     */
    protected $trans = null;

    /**
     * The system's current locale.
     *
     * @var string
     */
    protected $currentLocale = 'en';

    /**
     * The fallback locale to use.
     *
     * @var string
     */
    protected $fallbackLocale = 'en';

    public function __construct(Translator $translator)
    {
        $this->trans = $translator;
        $this->currentLocale = $translator->locale();
        $this->fallbackLocale = $translator->getFallback();
    }

    /**
     * Gets a collection of translation strings that should be patched in the Statamic Control Panel.
     *
     * @return array
     */
    public function getPatches()
    {
        if ($this->currentLocale == $this->fallbackLocale) {
            return [];
        }

        // Construct a path to our potential language directory.
        $localeDirectory = PathProvider::getResourcesDirectory('lang') . '/' . $this->currentLocale;
        $fallbackDirectory = PathProvider::getResourcesDirectory('lang') . '/' . $this->fallbackLocale;

        // If the configured locale exists, lets find which translation strings do not exist.
        if (Folder::exists($fallbackDirectory)) {

            $fallbackLocale = collect(Folder::getFiles($fallbackDirectory))->localize();
            $targetLocale = collect([]);

            if (Folder::exists($localeDirectory)) {
                $targetLocale = collect(Folder::getFiles($localeDirectory))->localize();
            }

            $targetFlat = $targetLocale->all();
            $patches = [];

            foreach ($fallbackLocale as $localeCategory => $categoryStrings) {
                if (is_array($categoryStrings)) {
                    foreach ($categoryStrings as $translationKey => $translationValue) {
                        if (array_key_exists($localeCategory, $targetFlat)) {

                            if (array_key_exists($translationKey, $targetFlat[$localeCategory]) == false) {
                                if (array_key_exists(Addon::CODE_ADDON_NAME . '::' . $localeCategory, $patches) == false) {
                                    $patches[Addon::CODE_ADDON_NAME . '::' . $localeCategory] = [];
                                }

                                $patches[Addon::CODE_ADDON_NAME . '::' . $localeCategory][$translationKey] = $translationValue;
                            }
                        } else {
                            if (array_key_exists(Addon::CODE_ADDON_NAME . '::' . $localeCategory, $patches) == false) {
                                $patches[Addon::CODE_ADDON_NAME . '::' . $localeCategory] = [];
                            }

                            $patches[Addon::CODE_ADDON_NAME . '::' . $localeCategory][$translationKey] = $translationValue;
                        }
                    }
                }
            }

            return $patches;
        }

        return [];
    }

}
