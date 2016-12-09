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
    return view('start');
});

Route::get('test', function () {
   $url = "https://www.hackmanit.de/trainings.html?query=string";
   $checkedUrl = parse_url($url);

   return $checkedUrl['scheme'] . '://' . $checkedUrl["host"] . $checkedUrl['path'] .  '?'. $checkedUrl['query'];

});
