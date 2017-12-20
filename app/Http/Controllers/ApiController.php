<?php

namespace App\Http\Controllers;

use App\HeaderReport;
use App\DomxssCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;

class ApiController extends Controller
{
    /**
     * Returns a very simple report for a single URL.
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function headerReport(Request $request) {
        $this->validate($request, [
            'url' => 'required|url'
        ]);

        $report = new HeaderReport($request->input('url'));
        return  collect([
                'checks' => [
                    'Content-Type' => [
                        'result' => (strpos( $report->getRating("content-type"), 'C' ) !== false),
                        'comment' => $report->getComment("content-type"),
                        'directive' => $report->getHeader( 'content-type' )
                    ],
                    'Content-Security-Policy' => [
                        'result' => (strpos( $report->getRating("content-security-policy"), 'C' ) !== false),
                        'comment' => $report->getComment("content-security-policy"),
                        'directive' => $report->getHeader( 'content-security-policy' )
                    ],
                    'Public-Key-Pins' => [
                        'result' => (strpos( $report->getRating("public-key-pins"), 'C' ) !== false),
                        'comment' => $report->getComment("public-key-pins"),
                        'directive' => $report->getHeader( 'public-key-pins' )
                    ],
                    'Strict-Transport-Security' => [
                        'result' => (strpos( $report->getRating("strict-transport-security"), 'C' ) !== false),
                        'comment' => $report->getComment("strict-transport-security"),
                        'directive' => $report->getHeader( 'strict-transport-security' )
                    ],
                    'X-Content-Type-Options' => [
                        'result' => (strpos( $report->getRating("x-content-type-options"), 'C' ) !== false),
                        'comment' => $report->getComment("x-content-type-options"),
                        'directive' => $report->getHeader( 'x-content-type-options' )
                    ],
                    'X-Frame-Options' => [
                        'result' => (strpos( $report->getRating("x-frame-options"), 'C' ) !== false),
                        'comment' => $report->getComment("x-frame-options"),
                        'directive' => $report->getHeader( 'x-frame-options' )
                    ],
                    'X-Xss-Protection' => [
                        'result' => (strpos( $report->getRating("x-xss-protection"), 'C' ) !== false),
                        'comment' => $report->getComment("x-xss-protection"),
                        'directive' => $report->getHeader( 'x-xss-protection' )
                    ]
                ]
            ]);
    }


    public function domxssReport(Request $request){
        
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'dangerLevel' => 'integer|min:0|max:10',
            'callbackurls' => 'required|array',
            'callbackurls.*' => 'url'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $check = new DomxssCheck($request->url);

        foreach ($request->callbackurls as $url) {
            
            try {
                $client = new Client();
                $client->post($url, [
                    'http_errors' => false,
                    'timeout' => 0.1,
                    'json' => $check->report()
                ]);
            }
            catch (\Exception $e) {
                \Log::debug($e);
            }
        }
       
        return "OK";
    }

}