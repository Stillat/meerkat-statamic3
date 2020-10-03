<?php

use Illuminate\Support\Facades\Route;
use Stillat\Meerkat\Addon;

Route::middleware('statamic.cp.authenticated')->group(function () {
    Route::group(['prefix' => Addon::getApiPrefix()], function () {

        Route::post('/', '\Stillat\Meerkat\Http\Controllers\Api\IndexController@index');

        Route::group(['prefix' => 'telemetry'], function () {
            Route::get('/', '\Stillat\Meerkat\Http\Controllers\Api\TelemetryController@index');
            Route::get('report', '\Stillat\Meerkat\Http\Controllers\Api\TelemetryController@getReport');
            Route::post('submit', '\Stillat\Meerkat\Http\Controllers\Api\TelemetryController@submitReport');
        });

        Route::group(['prefix' => 'comments'], function () {
            Route::post('/search', '\Stillat\Meerkat\Http\Controllers\Api\CommentsController@search');
            Route::post('/update', '\Stillat\Meerkat\Http\Controllers\Api\UpdateCommentController@updateComment');
            Route::post('/reply', '\Stillat\Meerkat\Http\Controllers\Api\ReplyCommentController@reply');
            Route::post('/publish', '\Stillat\Meerkat\Http\Controllers\Api\PublishCommentController@publishComment');
            Route::post('/publish-many', '\Stillat\Meerkat\Http\Controllers\Api\PublishCommentController@publishMany');
            Route::post('/unpublish', '\Stillat\Meerkat\Http\Controllers\Api\UnpublishCommentController@unPublishComment');
            Route::post('/unpublish-many', '\Stillat\Meerkat\Http\Controllers\Api\UnpublishCommentController@unPublishMany');
            Route::post('/remove', '\Stillat\Meerkat\Http\Controllers\Api\RemoveCommentController@deleteComment');
            Route::post('/remove-many', '\Stillat\Meerkat\Http\Controllers\Api\RemoveCommentController@deleteMany');
            Route::post('/report-spam', '\Stillat\Meerkat\Http\Controllers\Api\SpamController@markAsSpam');
            Route::post('/report-many-spam', '\Stillat\Meerkat\Http\Controllers\Api\SpamController@markManyAsSpam');
            Route::post('/report-ham', '\Stillat\Meerkat\Http\Controllers\Api\NotSpamController@markAsNotSpam');
            Route::post('/report-many-ham', '\Stillat\Meerkat\Http\Controllers\Api\NotSpamController@markManyAsNotSpam');

            Route::get('/check-for-spam', '\Stillat\Meerkat\Http\Controllers\Api\CheckForSpamController@checkForSpam');
        });

        Route::group(['prefix' => 'export'], function () {

            Route::get('csv', '\Stillat\Meerkat\Http\Controllers\Api\ExportController@csv');
            Route::get('json', '\Stillat\Meerkat\Http\Controllers\Api\ExportController@json');
        });

        Route::group(['prefix' => 'reporting'], function () {
            Route::get('overview', '\Stillat\Meerkat\Http\Controllers\Api\ReportingController@getReportOverview');
        });

    });
});
