<?php

use Stillat\Meerkat\Addon;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => Addon::ROUTE_PREFIX], function () {
   Route::get('/', 'DashboardController@index')->name('cp.meerkat.dashboard');
});
