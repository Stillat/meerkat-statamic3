<?php

use Stillat\Meerkat\Http\Emitter\Emit;
use Stillat\Meerkat\Translation\LanguagePatcher;
use Stillat\Meerkat\Statamic\ControlPanel\TranslationEmitter;


/** Do not edit below this line. This provides translation patches to the Control Panel. */

/** @var LanguagePatcher $languagePatcher */
$languagePatcher = app(LanguagePatcher::class);
$languagePatches = $languagePatcher->getPatches();

if ($languagePatches != null && is_array($languagePatches) && count($languagePatches) > 0) {
    Emit::js('i18n', function () use ($languagePatches) {
        return TranslationEmitter::getStatements($languagePatches);
    });
}
