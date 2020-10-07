<?php

use Illuminate\Support\Facades\Route;

Route::post('/!/Meerkat/socialize', '\Stillat\Meerkat\Http\Controllers\SocializeController@postSocialize');

include_once 'api.php';
include_once 'emit.php';
