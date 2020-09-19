<?php

use Illuminate\Support\Facades\Route;
use Stillat\Meerkat\Addon;

Route::group(['prefix' => Addon::getApiPrefix()], function () {

    Route::get('/', '\Stillat\Meerkat\Http\Controllers\Api\IndexController@index');

    Route::group(['prefix' => 'telemetry'], function () {
        Route::get('/', '\Stillat\Meerkat\Http\Controllers\Api\TelemetryController@index');
        Route::get('report', '\Stillat\Meerkat\Http\Controllers\Api\TelemetryController@getReport');
        Route::post('submit', '\Stillat\Meerkat\Http\Controllers\Api\TelemetryController@submitReport');
    });

    Route::group(['prefix' => 'comments'], function () {
        Route::get('/', '\Stillat\Meerkat\Http\Controllers\Api\CommentsController@search');
        Route::post('/update', '\Stillat\Meerkat\Http\Controllers\Api\UpdateCommentController@updateComment');
        Route::post('/reply', '\Stillat\Meerkat\Http\Controllers\Api\ReplyCommentController@reply');
        Route::post('/publish', '\Stillat\Meerkat\Http\Controllers\Api\PublishCommentController@publishComment');
        Route::post('/unpublish', '\Stillat\Meerkat\Http\Controllers\Api\UnpublishCommentController@unPublishComment');
        Route::post('/remove', '\Stillat\Meerkat\Http\Controllers\Api\RemoveCommentController@deleteComment');
        Route::post('/report-spam', '\Stillat\Meerkat\Http\Controllers\Api\SpamController@markAsSpam');
        Route::post('/report-ham', '\Stillat\Meerkat\Http\Controllers\Api\NotSpamController@markAsNotSpam');
    });

});
