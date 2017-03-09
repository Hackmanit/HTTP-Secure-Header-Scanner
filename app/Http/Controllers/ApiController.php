<?php

namespace App\Http\Controllers;

use App\Crawler;
use App\Jobs\AnalyzeSite;
use App\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ApiController extends Controller
{

    /**
     * Returns the Report for a single URL.
     *
     * @param Request $request (GET parameter "url")
     * @return array casted to json
     */
    public function singleReport(Request $request) {
        $this->validate($request, [
            'url' => 'required|url'
        ]);

        $report = (new Report($request->input('url')))->rate();
        return $report->getJson();

    }

    /**
     * Returns a very simple and report for a single URL.
     */
    public function siwecosReport(Request $request) {
        $this->validate($request, [
            'site' => 'required|url'
        ]);

        $report = new Report($request->input('site'));
        return  [
                'checks' => [
                    'Content-Type' => [
                        'result' => !(strpos( $report->getRating("content-type"), 'C' ) !== false),
                        'comment' => $report->getComment("content-type"),
                        'directive' => $report->getHeader( 'content-type' )
                    ],
                    'Content-Security-Policy' => [
                        'result' => !(strpos( $report->getRating("content-security-policy"), 'C' ) !== false),
                        'comment' => $report->getComment("content-security-policy"),
                        'directive' => $report->getHeader( 'content-security-policy' )
                    ],
                    'Public-Key-Pins' => [
                        'result' => !(strpos( $report->getRating("public-key-pins"), 'C' ) !== false),
                        'comment' => $report->getComment("public-key-pins"),
                        'directive' => $report->getHeader( 'public-key-pins' )
                    ],
                    'Strict-Transport-Security' => [
                        'result' => !(strpos( $report->getRating("strict-transport-security"), 'C' ) !== false),
                        'comment' => $report->getComment("strict-transport-security"),
                        'directive' => $report->getHeader( 'strict-transport-security' )
                    ],
                    'X-Content-Type-Options' => [
                        'result' => !(strpos( $report->getRating("x-content-type-options"), 'C' ) !== false),
                        'comment' => $report->getComment("x-content-type-options"),
                        'directive' => $report->getHeader( 'x-content-type-options' )
                    ],
                    'X-Frame-Options' => [
                        'result' => !(strpos( $report->getRating("x-frame-options"), 'C' ) !== false),
                        'comment' => $report->getComment("x-frame-options"),
                        'directive' => $report->getHeader( 'x-frame-options' )
                    ],
                    'X-Xss-Protection' => [
                        'result' => !(strpos( $report->getRating("x-xss-protection"), 'C' ) !== false),
                        'comment' => $report->getComment("x-xss-protection"),
                        'directive' => $report->getHeader( 'x-xss-protection' )
                    ]
                ]
            ];

    }


    /**
     * Returns a Collection|json with the crawled links.
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection links
     */
    public function crawler(Request $request) {
        $this->validate($request, [
            'url' => 'required|url',
            'anchor' => 'boolean',
            'image' => 'boolean',
            'media' => 'boolean',
            'link' => 'boolean',
            'script' => 'boolean',
            'area' => 'boolean',
            'frame' => 'boolean',
            'ignoreTlsErrors' => 'boolean',
            'proxy' => 'url'
        ]);

        $options = collect([]);
        if ($request->input("anchor") == true) $options->push("anchor");
        if ($request->input("image") == true) $options->push("image");
        if ($request->input("media") == true) $options->push("media");
        if ($request->input("link") == true) $options->push("link");
        if ($request->input("script") == true) $options->push("script");
        if ($request->input("area") == true) $options->push("area");
        if ($request->input("frame") == true) $options->push("frame");

        if ($request->input("ignoreTlsErrors") == true) $options->push("ignoreTLS");
        if ($request->has("proxy")) $options->put('proxy', $request->input('proxy'));

        $crawler = new Crawler("abcd", $request->input('url'), null, $options);
        $links = $crawler->extractAllLinks();

        return $links;
    }

}