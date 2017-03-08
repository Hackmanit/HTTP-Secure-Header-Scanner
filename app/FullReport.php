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
     * Generate the FullReport
     */
    public function generate() {
        Redis::hset($this->id, 'status', 'processing');

        // Generate single Reports
        $this->reports = collect();
        foreach ($this->urls as $url) {
            $this->reports->push(new Report($url));
        }

        // extract and group single ratings.
        $ratings = collect();
        /** @var Report $report */
        foreach ($this->reports as $report) {
            $report = $report->rate();
            $ratings->push([
                'url' => $report->url,
                'content-security-policy' => $report->getRating('content-security-policy'),
                'content-type' => $report->getRating('content-type'),
                'strict-transport-security' => $report->getRating('strict-transport-security'),
                'public-key-pins' => $report->getRating('public-key-pins'),
                'x-content-type-options' => $report->getRating('x-content-type-options'),
                'x-frame-options' => $report->getRating('x-frame-options'),
                'x-xss-protection' => $report->getRating('x-xss-protection')
            ]);
        }

        // TODO: FullReportRating ? Webapp rating is worst header rating?

        // Structure the returned values
        $return = collect([
            'id' => $this->id,
            'rating' => 'B', // TODO: Rating
            'Content-Security-Policy' => $ratings->groupBy('content-security-policy'),
            'Content-Type' => $ratings->groupBy('content-type'),
            'Strict-Transport-Security' => $ratings->groupBy('strict-transport-security'),
            'Public-Key-Pins' => $ratings->groupBy('public-key-pins'),
            'X-Content-Type-Options' => $ratings->groupBy('x-content-type-options'),
            'X-Frame-Options' => $ratings->groupBy('x-frame-options'),
            'X-Xss-Protection' => $ratings->groupBy('x-xss-protection')
        ]);

        return $return;
    }
}