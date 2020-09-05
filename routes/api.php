<?php

use Illuminate\Support\Facades\Route;
use Stillat\Meerkat\Addon;

Route::group(['prefix' => Addon::getApiPrefix()], function () {

    Route::get('/', 'Api\IndexController@index');

    Route::group(['prefix' => 'comments'], function () {
        Route::get('/', 'Api\CommentsController@search');
    });

});
