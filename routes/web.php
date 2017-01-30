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

Route::get('/', 'HeaderController@index');
Route::post('/', 'HeaderController@requestReport')->name("requestReport");
Route::get('/jsConfig', 'HeaderController@jsConfig');

Route::get('/{id}', 'HeaderController@displayReport');
