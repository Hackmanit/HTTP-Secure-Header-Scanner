<?php

namespace App;

use Illuminate\Support\Facades\Redis;

class FullReport {

    protected $id;
    protected $urls;
    protected $reports;

    // TODO: Combine FullReport with Crawler.
    public function __construct($id)
    {
        $this->id = $id;
        $this->urls = unserialize(Redis::hget($this->id, 'crawledUrls'));
        $this->doReports();
    }

    /**
     * Generate all $reports
     */
    protected function doReports() {
        Redis::hset($this->id, 'status', 'processing');
        \Log::critical($this->urls);
        $this->reports = collect();
        foreach ($this->urls as $url)
            $this->reports->push(new Report($url));
    }

    /**
     * Return the FullReport
     *
     * @return array
     */
    public function get() {

        $ContentSecurityPolicy = collect();
        $ContentType = collect();
        $PublicKeyPins = collect();
        $StrictTransportSecurity = collect();
        $XContentTypeOptions = collect();
        $XFrameOptions = collect();
        $XXSSProtection = collect();

        /** @var Report $report */
        foreach ($this->reports as $report) {
            $ContentSecurityPolicy->push([
                'url' => $report->url,
                'rating' => $report->ContentSecurityPolicyRating->getRating()
            ]);
            $ContentType->push([
                'url' => $report->url,
                'rating' => $report->ContentTypeRating->getRating()
            ]);
            $StrictTransportSecurity->push([
                'url' => $report->url,
                'rating' => $report->HttpStrictTransportSecurityRating->getRating()
            ]);
            $PublicKeyPins->push([
                'url' => $report->url,
                'rating' => $report->HttpPublicKeyPinningRating->getRating()
            ]);
            $XContentTypeOptions->push([
                'url' => $report->url,
                'rating' => $report->XContentTypeOptionsRating->getRating()
            ]);
            $XFrameOptions->push([
                'url' => $report->url,
                'rating' => $report->XFrameOptionsRating->getRating()
            ]);
            $XXSSProtection->push([
                'url' => $report->url,
                'rating' => $report->XXSSProtectionRating->getRating()
            ]);
        }

        return [
            'id' => Redis::hget($this->id, 'status'),
            'rating' => '', // TODO: FullReportRating.
            'Content-Security-Policy' => $ContentSecurityPolicy,
            'Content-Type' => $ContentType,
            'Strict-Transport-Security' => $StrictTransportSecurity,
            'Public-Key-Pins' => $PublicKeyPins,
            'X-Content-Type-Options' => $XContentTypeOptions,
            'X-Frame-Options' => $XFrameOptions,
            'X-Xss-Protection' => $XXSSProtection
        ];
    }

}