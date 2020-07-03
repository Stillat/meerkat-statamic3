<?php

use Stillat\Meerkat\Http\Emitter\Emit;

Emit::cpCss('test', function () {
    return '/* CSS HERE */';
});


/*
Route::get('/meerkat/css/test.css', function () {
    return 'asdf';
});