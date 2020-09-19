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
        Route::post('/update', 'Api\UpdateCommentController@updateComment');
        Route::post('/publish', 'Api\PublishCommentController@publishComment');
        Route::post('/unpublish', 'Api\UnpublishCommentController@unPublishComment');
        Route::post('/remove', 'Api\RemoveCommentController@deleteComment');
        Route::post('/report-spam', 'Api\SpamController@markAsSpam');
        Route::post('/report-ham', 'Api\NotSpamController@markAsNotSpam');
    });

});
