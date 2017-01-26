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
    public function report(Request $request) {
        $this->validate($request, [
            'site' => 'required|url'
        ]);

        return new Report($request->input('site'));
    }


    /**
     * Returns a very simple and report for a single URL.
     */
    public function siwecosReport(Request $request) {
        $this->validate($request, [
            'site' => 'required|url'
        ]);

        $report = new Report($request->input('site'));

        $customReport = [
            'checks' => [
                'Content-Type' => [
                    'result' => !(strpos($report->ContentTypeRating->getRating(), 'C') !== false),
                    'comment' => $report->ContentTypeRating->getComment(),
                    'directive' => $report->ContentTypeRating->getHeader('content-type')
                ],
                'Content-Security-Policy' => [
                    'result' => !(strpos($report->ContentSecurityPolicyRating->getRating(), 'C') !== false),
                    'comment' => $report->ContentSecurityPolicyRating->getComment(),
                    'directive' => $report->ContentSecurityPolicyRating->getHeader('content-security-policy')
                ],
                'Public-Key-Pins' => [
                    'result' => !(strpos($report->HttpPublicKeyPinningRating->getRating(), 'C') !== false),
                    'comment' => $report->HttpPublicKeyPinningRating->getComment(),
                    'directive' => $report->HttpPublicKeyPinningRating->getHeader('public-key-pins')
                ],
                'Strict-Transport-Security' => [
                    'result' => !(strpos($report->HttpStrictTransportSecurityRating->getRating(), 'C') !== false),
                    'comment' => $report->HttpStrictTransportSecurityRating->getComment(),
                    'directive' => $report->HttpStrictTransportSecurityRating->getHeader('strict-transport-security')
                ],
                'X-Content-Type-Options' => [
                    'result' => !(strpos($report->XContentTypeOptionsRating->getRating(), 'C') !== false),
                    'comment' => $report->XContentTypeOptionsRating->getComment(),
                    'directive' => $report->XContentTypeOptionsRating->getHeader('x-content-type-options')
                ],
                'X-Frame-Options' => [
                    'result' => !(strpos($report->XFrameOptionsRating->getRating(), 'C') !== false),
                    'comment' => $report->XFrameOptionsRating->getComment(),
                    'directive' => $report->XFrameOptionsRating->getHeader('x-frame-options')
                ],
                'X-Xss-Protection' => [
                    'result' => !(strpos($report->XXSSProtectionRating->getRating(), 'C') !== false),
                    'comment' => $report->XXSSProtectionRating->getComment(),
                    'directive' => $report->XXSSProtectionRating->getHeader('x-xss-protection')
                ]
            ]
        ];

        return response()->json($customReport);
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