<?php

use Stillat\Meerkat\Meerkat;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => Meerkat::ROUTE_PREFIX], function () {
   Route::get('/', 'DashboardController@index')->name('cp.meerkat.dashboard');
});
