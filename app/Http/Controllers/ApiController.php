<?php

namespace App\Http\Controllers;

use App\Crawler;
use App\Jobs\CrawlerJob;
use App\Jobs\GenerateFullReportJob;
use App\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\URL;

class ApiController extends Controller
{

    /**
     * Returns the Report for a single URL.
     *
     * @param Request $request (GET parameter "url")
     * @return \Illuminate\Support\Collection
     */
    public function singleReport(Request $request) {
        $this->validate($request, [
            'url' => 'required|url'
        ]);

        $report = (new Report($request->input('url')))->rate();
        return $report->get();
    }

    /**
     * Dispatches a job for a multipleReport and returns the reportUrl.
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function multipleReport(Request $request) {
        $this->validate($request, [
            'urls' => 'required|array',
            'urls.*' => 'url'
        ]);

        $id = str_random();
        $this->dispatch(new GenerateFullReportJob($id, collect($request->input('urls'))));

        return collect([
            'id' => $id,
            'reportUrl' => route('downloadReport', $id)
        ]);
    }

    /**
     * Retrieves the fullReport for the $id from redis and returns it as json.
     * If the fullReport is not ready yet, it returns a status update.
     *
     * @param $id
     * @return \Illuminate\Support\Collection
     */
    public function downloadReport($id) {
        $status = Redis::hget($id, 'status');

        if ($status == 'finished') {
            $fullReport = unserialize(Redis::hget($id, 'fullReport'));
            return collect([
                'id' => $id,
                'status' => $status,
                'amountUrlsTotal' => Redis::hget($id, 'amountUrlsTotal'),
                'amountGeneratedReports' => Redis::hget($id, 'amountReportsGenerated'),
                'fullReport' => $fullReport
            ]);
        }
        return collect([
            'id' => $id,
            'status' => $status,
            'amountUrlsTotal' => Redis::hget($id, 'amountUrlsTotal'),
            'amountGeneratedReports' => Redis::hget($id, 'amountReportsGenerated')
        ]);
    }

    /**
     * Returns a very simple report for a single URL.
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function siwecosReport(Request $request) {
        $this->validate($request, [
            'url' => 'required|url'
        ]);

        $report = new Report($request->input('url'));
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

    /**
     * Returns a Collection|json with the crawled links.
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection links
     */
    public function crawler(Request $request) {
        $this->validate($request, [
            'url' => 'required',
            'anchor' => 'boolean',
            'image' => 'boolean',
            'media' => 'boolean',
            'link' => 'boolean',
            'script' => 'boolean',
            'area' => 'boolean',
            'frame' => 'boolean',
            'ignoreTlsErrors' => 'boolean',
            'proxy' => 'url|nullable',
            'limit' => 'integer',
            'whitelist' => 'array|nullable',
            'whitelist.*' => 'url',
            'customElements' => 'json|nullable'
        ]);

        $options = collect([]);
        $whitelist = null;

        if ($request->input("anchor") == true) $options->push("anchor");
        if ($request->input("image") == true) $options->push("image");
        if ($request->input("media") == true) $options->push("media");
        if ($request->input("link") == true) $options->push("link");
        if ($request->input("script") == true) $options->push("script");
        if ($request->input("area") == true) $options->push("area");
        if ($request->input("frame") == true) $options->push("frame");

        if ($request->has("customElements")) $options->put("customElements", $request->input("customElements"));

        if ($request->input("ignoreTlsErrors") == true) $options->push("ignoreTLS");
        if ($request->has("proxy")) $options->put("proxy", $request->input("proxy"));
        if ($request->has("limit")) $options->put("limit", $request->input("limit"));

        if ($request->has("whitelist")) {
            $whitelist = collect($request->input("whitelist"));
            $whitelist = $whitelist->map(function($value, $key) {
               return parse_url($value, PHP_URL_HOST);
            });
        }

        $id = str_random();
        $this->dispatch(new CrawlerJob($id, $request->input('url'), $whitelist, $options));

        return collect([
            'status' => Redis::hget($id, 'status'),
            'id' => $id,
            'linksUrl' => route('downloadLinks', $id)
        ]);
    }

    /**
     * Returns the cralwed links from the redis cache.
     *
     * @param $id
     * @return \Illuminate\Support\Collection
     */
    public function crawledLinks($id) {
        $status = Redis::hget($id, 'status');

        if ($status == 'crawlerFinished') {
            $links = unserialize(Redis::hget($id, 'crawledLinks'));
            return collect([
                'id' => $id,
                'status' => $status,
                'amountUrlsCrawled' => Redis::hget($id, 'amountUrlsCrawled'),
                'amountUrlsToCrawl' => Redis::hget($id, 'amountUrlsToCrawl'),
                'links' => $links
            ]);
        }
        return collect([
            'id' => $id,
            'status' => $status,
            'amountUrlsCrawled' => Redis::hget($id, 'amountUrlsCrawled'),
            'amountUrlsToCrawl' => Redis::hget($id, 'amountUrlsToCrawl'),
        ]);
    }

}