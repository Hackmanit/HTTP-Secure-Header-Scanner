<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeSite;
use App\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ApiController extends Controller
{

    /**
     * Scan request
     *
     * @param Request $request
     * @return array
     */
    public function scan(Request $request) {
        $this->validate($request, [
            'site' => 'required|url'
        ]);

        $id = str_random();

        // TODO: Options via POST Request
        $this->dispatch(new AnalyzeSite($id, $request->input('site'), collect(['hackmanit.de']), collect(['anchors', 'images', 'links', 'scripts', 'media', 'area', 'frames'])));

        return [
            'id' => $id,
            'status' => Redis::hget($id, 'status')
        ];
    }

    /**
     * Get the specific request
     *
     * @param Request $request
     * @return array
     */
    public function get(Request $request) {
        $this->validate($request, [
            'id' => 'required|string|size:16'
        ]);

        $id = $request->input('id');

        if (Redis::hget($id, 'status') !== 'finished')
            return [
                'id' => $id,
                'status' => Redis::hget($id, 'status')
            ];

        return [
            'id' => $id,
            'status' => Redis::hget($id, 'status'),
            'report' => unserialize(Redis::hget($id, 'report'))
        ];

    }

    /**
     * Returns the Report for a single URL.
     *
     * @param Request $request (GET parameter "site")
     * @return Report
     */
    public function rate(Request $request) {
        $this->validate($request, [
            'site' => 'required|url'
        ]);

        $report = new Report($request->input('site'));
        if( $report->status == "success")
            return $report;

        return response()->json( [
            "status" => "error"
        ]);
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
     * Returns an array with the crawled links.
     *
     * @param Request $request
     */
    public function links(Request $request) {
        // TODO: Implement crawler return
    }

}