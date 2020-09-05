<?php

use Stillat\Meerkat\Http\RequestHelpers;
use Stillat\Meerkat\Http\Emitter\Emit;
use Stillat\Meerkat\Statamic\ControlPanel\TranslationEmitter;
use Stillat\Meerkat\Translation\LanguagePatcher;
use Stillat\Meerkat\Statamic\ControlPanel\SettingsProvider;

$allowDirectAccess = config('meerkat.internals.allowDirectAccessToCpJsEmissions', false);

if ($allowDirectAccess || RequestHelpers::isControlPanelRequestFromHeaders(request())) {

    /** @var LanguagePatcher $languagePatcher */
    $languagePatcher = app(LanguagePatcher::class);
    $languagePatches = $languagePatcher->getPatches();

    if ($languagePatches != null && is_array($languagePatches) && count($languagePatches) > 0) {
        Emit::js('i18n', function () use ($languagePatches) {
            return TranslationEmitter::getStatements($languagePatches);
        });
    }

    Emit::js('cpConfiguration', function () {
        /** @var SettingsProvider $settingsProvider */
        $settingsProvider = app(SettingsProvider::class);

        return $settingsProvider->emitStatements();
    });

}
