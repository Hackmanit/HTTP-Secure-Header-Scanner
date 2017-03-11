<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

/**
 * Only runs as a background job. See Jobs/AnalyzeSite.
 */
class FullReport {

    protected $id;
    protected $urls;
    protected $ratings;
    protected $fullRating;
    protected $comment;

    public function __construct($id, Collection $urls)
    {
        $this->id = $id;
        $this->urls = $urls;
    }

    /**
     * Generate and return the FullReport
     *
     * @return Collection
     */
    public function generate() {
        // Update the GenerateFullReportJob status
        Redis::hset($this->id, 'status', 'processing');
        Redis::hset($this->id, 'amountUrlsTotal', $this->urls->count());

        // Create singleReports
        $this->ratings = $this->getGroupedHeaderRatings();
        $this->runFullReportRating();

        return collect([
            'fullRating' => $this->fullRating,
            'comment' => $this->comment,
            'header' => $this->ratings,
            'worstHeaderRatings' => $this->getWorstHeaderRatings(),
        ]);
    }

    /**
     * Calculates the FullReport rating for all scanned sites.
     *
     * @param Collection $singleRatings
     * @return string FullReport rating
     *
     * TODO: Content-Type Rating ?
     */
    protected function runFullReportRating() {
        // Class C Rating - insecure
        $fullRating = "C";

        // Class A Rating - high security
        if (
            ($this->getNumericWorstRating('Strict-Transport-Security') >= 7) && // min. A
            ($this->getNumericWorstRating('X-Content-Type-Options') >= 7) && // min. A
            ($this->getNumericWorstRating('X-Frame-Options') >= 7) && // min. A
            ($this->getNumericWorstRating('X-Xss-Protection') >= 4) && // min. B
            ($this->getNumericWorstRating('Content-Security-Policy') >= 4) // min. B
        ) {
            $fullRating = "A";
        }
        // Class B Rating - medium security
        elseif (
            ($this->getNumericWorstRating('Strict-Transport-Security') >= 4) && // min. A
            ($this->getNumericWorstRating('X-Content-Type-Options') >= 4) && // min. A
            ($this->getNumericWorstRating('Content-Security-Policy') >= 4) // min. B
        ) {
            $fullRating = "B";
        }

        // Optional "+" ratings
        if ($this->getNumericWorstRating('Public-Key-Pins') >= 4) $fullRating .= "+";
        if ($this->getNumericWorstRating('Content-Security-Policy') >= 7) $fullRating .= "+";

        $this->fullRating = $fullRating;
    }

    /**
     * Returns a Collection with all single Report ratings.
     *
     * @return Collection $ratings
     */
    protected function getGroupedHeaderRatings() {
        // Generate single Reports
        $ratings = collect();
        $counter = 0;
        foreach ($this->urls as $url) {
            $report = (new Report($url))->rate();
            $ratings->push(['header' => 'Content-Security-Policy', 'url' => $url, 'rating' => $report->getRating('Content-Security-Policy')]);
            $ratings->push(['header' => 'Content-Type', 'url' => $url, 'rating' => $report->getRating('Content-Type')]);
            $ratings->push(['header' => 'Public-Key-Pins', 'url' => $url, 'rating' => $report->getRating('Public-Key-Pins')]);
            $ratings->push(['header' => 'Strict-Transport-Security', 'url' => $url, 'rating' => $report->getRating('Strict-Transport-Security')]);
            $ratings->push(['header' => 'X-Content-Type-Options', 'url' => $url, 'rating' => $report->getRating('X-Content-Type-Options')]);
            $ratings->push(['header' => 'X-Frame-Options', 'url' => $url, 'rating' => $report->getRating('X-Frame-Options')]);
            $ratings->push(['header' => 'X-Xss-Protection', 'url' => $url, 'rating' => $report->getRating('X-Xss-Protection')]);

            $counter++;
            Redis::hset($this->id, 'amountReportsGenerated', $counter);
        }

        return $ratings->groupBy('header');
    }

    /**
     * Returns the worst out of all rating values for the $header as an integer.
     *
     * @param $header String The rated header
     * @return string Worst rating.
     */
    protected function getNumericWorstRating($header) {
        /** @var Collection $ratings */
        $ratings = $this->ratings->get($header)->groupBy('rating');
        if($ratings->has("C")) return 1;
        if($ratings->has("C+")) return 2;
        if($ratings->has("C++")) return 3;
        if($ratings->has("B")) return 4;
        if($ratings->has("B+")) return 5;
        if($ratings->has("B++")) return 6;
        if($ratings->has("A")) return 7;
        if($ratings->has("A+")) return 8;
        if($ratings->has("A++")) return 9;
    }

    /**
     * Returns the worst out of all rating values for the $header as a string.
     *
     * @param $header String The rated header
     * @return string Worst rating.
     */
    protected function getWorstRating($header) {
        /** @var Collection $ratings */
        $ratings = $this->ratings->get($header)->groupBy('rating');
        if($ratings->has("C"))      return "C";
        if($ratings->has("C+"))     return "C+";
        if($ratings->has("C++"))    return "C++";
        if($ratings->has("B"))      return "B";
        if($ratings->has("B+"))     return "B+";
        if($ratings->has("B++"))    return "B++";
        if($ratings->has("A"))      return "A";
        if($ratings->has("A+"))     return "A+";
        if($ratings->has("A++"))    return "A++";
    }

    /**
     * Returns a Collection with the worst ratings for a specific $header
     *
     * @return Collection WorstRatings
     */
    protected function getWorstHeaderRatings() {
        return collect([
            "Content-Security-Policy" => $this->getWorstRating("Content-Security-Policy"),
            "Content-Type" => $this->getWorstRating("Content-Type"),
            "Public-Key-Pins" => $this->getWorstRating("Public-Key-Pins"),
            "Strict-Transport-Security" => $this->getWorstRating("Strict-Transport-Security"),
            "X-Content-Type-Options" => $this->getWorstRating("X-Content-Type-Options"),
            "X-Frame-Options" => $this->getWorstRating("X-Frame-Options"),
            "X-Xss-Protection" => $this->getWorstRating("X-Xss-Protection"),
        ]);
    }
}