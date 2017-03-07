<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

/**
 * Only runs as a background job. See Jobs/AnalyzeSite.
 *
 * Class FullReport
 * @package App
 */
class FullReport {

    protected $id;
    protected $urls;
    protected $reports;

    public function __construct($id, Collection $urls)
    {
        $this->id = $id;
        $this->urls = $urls;
    }

    /**
     * Generate all $reports
     */
    public function rate() {
        Redis::hset($this->id, 'status', 'processing');

        // Generate Reports
        $this->reports = collect();
        foreach ($this->urls as $url) {
            $this->reports->push(new Report($url));
        }

        $ContentSecurityPolicy = collect();
        $ContentType = collect();
        $PublicKeyPins = collect();
        $StrictTransportSecurity = collect();
        $XContentTypeOptions = collect();
        $XFrameOptions = collect();
        $XXSSProtection = collect();

        /** @var Report $report */
        foreach ($this->reports as $report) {
            $report = $report->rate();
            $ContentSecurityPolicy->push([
                'url' => $report->url,
                'rating' => $report->getRating('content-security-policy')
            ]);
            $ContentType->push([
                'url' => $report->url,
                'rating' => $report->getRating('content-type')
            ]);
            $StrictTransportSecurity->push([
                'url' => $report->url,
                'rating' => $report->getRating('strict-transport-security')
            ]);
            $PublicKeyPins->push([
                'url' => $report->url,
                'rating' => $report->getRating('public-key-pins')
            ]);
            $XContentTypeOptions->push([
                'url' => $report->url,
                'rating' => $report->getRating('x-content-type-options')
            ]);
            $XFrameOptions->push([
                'url' => $report->url,
                'rating' => $report->getRating('x-frame-options')
            ]);
            $XXSSProtection->push([
                'url' => $report->url,
                'rating' => $report->getRating('x-xss-protection')
            ]);
        }

        // TODO: FullReportRating ? Webapp rating is worst header rating?


        // Structure the returned values
        $return = collect([
            'id' => $this->id,
            'rating' => 'B', // TODO: Rating
            'Content-Security-Policy' => $ContentSecurityPolicy,
            'Content-Type' => $ContentType,
            'Strict-Transport-Security' => $StrictTransportSecurity,
            'Public-Key-Pins' => $PublicKeyPins,
            'X-Content-Type-Options' => $XContentTypeOptions,
            'X-Frame-Options' => $XFrameOptions,
            'X-Xss-Protection' => $XXSSProtection
        ]);

        return $return;
    }
}