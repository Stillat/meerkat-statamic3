<?php

use Illuminate\Support\Facades\Route;
use Stillat\Meerkat\Addon;

Route::group(['prefix' => Addon::getApiPrefix()], function () {

    Route::get('/', 'Api\IndexController@index');

    Route::group(['prefix' => 'telemetry'], function () {
        Route::get('/', 'Api\TelemetryController@index');
        Route::get('report', 'Api\TelemetryController@getReport');
        Route::post('submit', 'Api\TelemetryController@submitReport');
    });

    Route::group(['prefix' => 'comments'], function () {
        Route::get('/', 'Api\CommentsController@search');
        Route::post('/publish', 'Api\CommentsController@publishComment');
        Route::post('/unpublish', 'Api\CommentsController@unPublishComment');
        Route::post('/remove', 'Api\CommentsController@deleteComment');
    });

});
