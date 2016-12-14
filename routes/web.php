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

Route::post('/', ['as' => 'requestReport', 'uses' => 'HeaderController@requestReport']);
Route::get('report/{id}', ['as' => 'displayReport', 'uses' => 'HeaderController@displayReport']);

Route::get('/test', function() {
    $crawler = new App\Crawler("xyz", "https://youtube.com", collect(["youtube.com"]), collect(['proxy' => 'tcp://172.18.0.1:8888', 'ignoreTLS']), 11);
    $crawler->extractAllLinks();
});