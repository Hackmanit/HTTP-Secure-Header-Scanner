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

Route::get('/test', function() {
    $run = true;
    $numbers = [0, 1, 2, 3, 4, 5];
    $i = 6;

    while ($run) {
        foreach ($numbers as $number) {
            echo "{$number}<br>\n";
            unset($numbers[$number]);

            if ($i < 100) {
                $numbers[] = $i++;
            }
        }

        if (count($numbers) <= 0) {
            $run = false;
        }
    }
});

Route::get('/{id}', 'HeaderController@displayReport');
