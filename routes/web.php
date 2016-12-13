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

Route::get('/', function () {
    return view('enter');
});

Route::post('/', ['as' => 'requestReport', 'uses' => 'HeaderController@requestReport']);
Route::get('report/{id}', ['as' => 'displayReport', 'uses' => 'HeaderController@displayReport']);

Route::get('/test', function() {
    dd(Redis::hkeys("inppH919UWVLTRBN"));
});