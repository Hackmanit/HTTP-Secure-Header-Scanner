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

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

 Route::group(['prefix' => 'v1'], function () {
     Route::get('/analyze', 'HeaderController@show');
     Route::get('/links', 'HeaderController@links');
     Route::get('/links/optimized', 'HeaderController@linksOptimized');
     Route::get('/test', function() {
        return parse_url("javascript:slashdesign.it/test", PHP_URL_HOST);
     });
 });