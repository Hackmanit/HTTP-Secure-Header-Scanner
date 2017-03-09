<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function () {
    Route::get('/rate', 'ApiController@singleReport');
    Route::get('/crawl', 'ApiController@crawler');

    //Route::get('/scan', 'ApiController@scan');
    //Route::get('/report', 'ApiController@get');

    Route::get('/siwecos/rate', 'ApiController@siwecosReport');
});