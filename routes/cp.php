<?php

use Stillat\Meerkat\Addon;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => Addon::ROUTE_PREFIX], function () {
   Route::get('/', 'DashboardController@index')->name('cp.meerkat.dashboard');
   Route::get('blueprint', 'MeerkatBlueprintController@edit')->name('cp.meerkat.blueprint');
   Route::patch('blueprint', 'MeerkatBlueprintController@update')->name('cp.meerkat.blueprint.update');

   Route::group(['prefix' => 'error-logs'], function () {
       Route::get('/','ErrorLogsController@index');
       Route::get('logs','ErrorLogsController@getLogs');
       Route::post('remove-logs','ErrorLogsController@postRemoveAllLogs');
       Route::post('remove-log-instance','ErrorLogsController@postRemoveLogInstance');
   });

});

