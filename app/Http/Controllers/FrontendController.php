<?php

namespace App\Http\Controllers;

class FrontendController extends Controller
{
    /**
     * Return frontend.
     */
    public function index() {
        return view('start');
    }

    /**
     * jsConfig for the frontend.
     *
     * @return array with settings.
     */
    public function jsConfig() {
        return [
            'LIMIT' => env("LIMIT", 1000),
            'HOST_IP' => exec("/sbin/ip route|awk '/default/ { print $3 }'"),
            'CUSTOM_JSON' => [
                "a"  => "href",
                "img" => "src",
                "link" => "href",
                "script" => "src",
                "video" => "src",
                "audio" => "src",
                "source" => "src",
                "area" => "href",
                "iframe" => "src",
                "frame" => "src"
            ]
        ];
    }

}