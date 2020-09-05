<?php

// /!/ Endpoints are just there for backwards compatibility.

Route::post('/!/Meerkat/delete', function () {

});

Route::post('/!/Meerkat/update', function () {

});

Route::post('/!/Meerkat/socialize', '\Stillat\Meerkat\Http\Controllers\SocializeController@postSocialize');

Route::get('/!/Meerkat/test', function () {
    return 'testing Legacy Route Structure.';
});

include_once 'api.php';
include_once 'emit.php';
