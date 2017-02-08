<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', 'FrontendController@index');
Route::post('/', 'FrontendController@requestReport')->name("requestReport");
Route::get('/jsConfig', 'FrontendController@jsConfig');

Route::get('/single', 'FrontendController@singleReport');

Route::get('/{id}', 'FrontendController@displayReport');
Route::get('/{id}/details', 'FrontendController@retrieveReport');
