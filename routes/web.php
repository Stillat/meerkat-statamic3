<?php

use Stillat\Meerkat\Http\Emitter\Emit;
use Stillat\Meerkat\Translation\LanguagePatcher;
use Stillat\Meerkat\Statamic\ControlPanel\TranslationEmitter;

Route::post('/!/Meerkat/delete', function () {

});

Route::post('/!/Meerkat/update', function () {

});

Route::post('/!/Meerkat/socialize', '\Stillat\Meerkat\Http\Controllers\SocializeController@postSocialize');

Route::get('/!/Meerkat/test', function () {
    return 'testing Legacy Route Structure.';
});

/** Do not edit below this line. This provides translation patches to the Control Panel. */

/** @var LanguagePatcher $languagePatcher */
$languagePatcher = app(LanguagePatcher::class);
$languagePatches = $languagePatcher->getPatches();

if ($languagePatches != null && is_array($languagePatches) && count($languagePatches) > 0) {
    Emit::js('i18n', function () use ($languagePatches) {
        return TranslationEmitter::getStatements($languagePatches);
    });
}
