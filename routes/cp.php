<?php

use Illuminate\Support\Facades\Route;
use Stillat\Meerkat\Addon;

Route::group(['prefix' => Addon::ROUTE_PREFIX], function () {
    Route::get('/', '\Stillat\Meerkat\Http\Controllers\DashboardController@index')->name('cp.meerkat.dashboard');
    Route::get('redirect/{entryId}/{commentId}', '\Stillat\Meerkat\Http\Controllers\DashboardController@redirectToEntry');


    Route::get('blueprint', '\Stillat\Meerkat\Http\Controllers\MeerkatBlueprintController@edit')->name('cp.meerkat.blueprint');
    Route::patch('blueprint', '\Stillat\Meerkat\Http\Controllers\MeerkatBlueprintController@update')->name('cp.meerkat.blueprint.update');

    Route::group(['prefix' => 'error-logs'], function () {
        Route::get('/', '\Stillat\Meerkat\Http\Controllers\ErrorLogsController@index');
        Route::get('logs', '\Stillat\Meerkat\Http\Controllers\ErrorLogsController@getLogs');
        Route::post('remove-logs', '\Stillat\Meerkat\Http\Controllers\ErrorLogsController@postRemoveAllLogs');
        Route::post('remove-log-instance', '\Stillat\Meerkat\Http\Controllers\ErrorLogsController@postRemoveLogInstance');
    });

    Route::get('/{filter}', '\Stillat\Meerkat\Http\Controllers\DashboardController@dashboardWithFilter')->name('cp.meerkat.filteredDashboard');
});
