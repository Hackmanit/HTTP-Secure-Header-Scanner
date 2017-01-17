<?php

namespace App\Http\Controllers;

use App\Report;
use Illuminate\Http\Request;

class ApiController extends Controller
{

    /**
     * Returns the Report for a single URL.
     *
     * @param Request $request (GET parameter "site")
     * @return Report
     */
    public function report(Request $request) {
        $this->validate($request, [
            'site' => 'required|url'
        ]);

        return new Report($request->input('site'));
    }

    /**
     * Returns an array with the crawled links.
     *
     * @param Request $request
     */
    public function links(Request $request) {
        // TODO: Implement crawler return
    }

}