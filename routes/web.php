<?php

use Illuminate\Support\Facades\Route;

Route::post('/!/Meerkat/socialize', '\Stillat\Meerkat\Http\Controllers\SocializeController@postSocialize');

// /!/ Endpoints are just there for backwards compatibility.
// TODO: don't use closures to allow for route optimization.
/*
Route::post('/!/Meerkat/delete', function () {

});

Route::post('/!/Meerkat/update', function () {

});


Route::get('/!/Meerkat/test', function () {
    return 'testing Legacy Route Structure.';
});
*/
include_once 'api.php';
include_once 'emit.php';
