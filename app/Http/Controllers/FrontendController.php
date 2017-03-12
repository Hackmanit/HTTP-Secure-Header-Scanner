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
            'LIMIT' => env("LIMIT", 100),
            'HOST_IP' => exec("/sbin/ip route|awk '/default/ { print $3 }'")
        ];
    }

}