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
    $crawler = new App\Crawler(str_random(), "https://hackmanit.de", collect(["hackmanit.de", "www.hackmanit.de"]), collect(), 1000);
    $crawler->extractAllLinks();
});

Route::get('/testUnparseUrl', function () {
    function unparse_url($parsed_url, $scanned_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : 'http://';
        $host = isset($parsed_url['host']) ? strtolower($parsed_url['host']) : strtolower(parse_url($scanned_url, PHP_URL_HOST));
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        if (strncmp($path, "/", 1) !== 0) $path = "/" . $path;
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        return "$scheme$user$pass$host$port$path$query";
    }

    $parsed_url = parse_url('//www.hackmanit.de/demo/sichere-webentwicklung-teaser.pdf');
    return unparse_url($parsed_url, 'www.hackmanit.de');
});