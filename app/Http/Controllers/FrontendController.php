<?php

namespace App\Http\Controllers;

use App\FullReport;
use App\Report;
use Illuminate\Http\Request;
use App\Http\Requests\ReportRequest;
use App\Jobs\AnalyzeSite;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class FrontendController extends Controller
{
    /**
     * Return frontend.
     */
    public function index() {
        return view('start');
    }

    /**
     * Requesting a Report.
     *
     * @param ReportRequest $request
     * @return array
     */
    public function requestReport(ReportRequest $request)
    {
        $url = $request->input('url');
        if (substr($url, -1) !== '/')
            $url = $url . '/';

        // whitelist
        $whiteList = collect(explode("\n", $request->input('whitelist')))->flatten()->filter(function ($value) {
            if ($value !== null && $value != "")
                return true;
            return false;
        });

        // Set options for crawler
        $options = collect([]);
        if ($request->has('proxy'))
            $options->put('proxy', $request->input('proxyAddress'));
        if ($request->has('ignoreTLS'))
            $options->push('ignoreTLS');
        if($request->has('scan'))
            foreach ($request->input('scan') as $type)
                $options->push($type);
        if ($request->has('doNotCrawl'))
            $options->push('doNotCrawl');

        $options->put('limit', $request->input('limit'));

        $id = str_random();
        Redis::hset($id, "url", $url);
        dispatch(new AnalyzeSite($id, $url, $whiteList, $options));

        return redirect()->to('/' . $id);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayReport($id) {

        $url = Redis::hget($id, "url");
        return view("report", [ "url" => $url]);
    }

    /**
     * jsConfig for the frontend.
     *
     * @return array with settings.
     */
    public function jsConfig() {
        return [
            'LIMIT' => env("LIMIT", 1000),
            'HOST_IP' => exec("/sbin/ip route|awk '/default/ { print $3 }'"),
            'CUSTOM_JSON' => [
                "a"  => "href",
                "img" => "src",
                "link" => "href",
                "script" => "src",
                "video" => "src",
                "audio" => "src",
                "source" => "src",
                "area" => "href",
                "iframe" => "src",
                "frame" => "src"
            ]
        ];
    }

    /**
     * The report site asks every x seconds for this report if the status != finished.
     *
     *
     * @param $id
     * @return mixed
     */
    public function retrieveReport($id) {
        $fullreport = unserialize(Redis::hget($id, "fullreport"));

        return [
            "id" => $id,
            "status" => Redis::hget($id, "status"),
            "fullreport" => $fullreport,
            "headerRatings" => $this->getOverallHeaderRatings($fullreport)
        ];
    }

    public function singleReport(Request $request) {
        $url = $request->input('url');
        if (substr($url, -1) !== '/')
            $url = $url . '/';

        return (new Report($url))->rate();
    }

    /**
     * Returns the worst header ratings for the frontend.
     *
     * @param Collection $fullreport
     * @return Collection WorstRatings
     */
    protected function getOverallHeaderRatings(Collection $fullreport) {
        return collect([
            "Content-Security-Policy" => $this->getWorstRating($fullreport->get("Content-Security-Policy")->groupBy('rating')),
            "Content-Type" => $this->getWorstRating($fullreport->get("Content-Type")->groupBy('rating')),
            "Public-Key-Pins" => $this->getWorstRating($fullreport->get("Public-Key-Pins")->groupBy('rating')),
            "Strict-Transport-Security" => $this->getWorstRating($fullreport->get("Strict-Transport-Security")->groupBy('rating')),
            "X-Content-Type-Options" => $this->getWorstRating($fullreport->get("X-Content-Type-Options")->groupBy('rating')),
            "X-Frame-Options" => $this->getWorstRating($fullreport->get("X-Frame-Options")->groupBy('rating')),
            "X-Xss-Protection" => $this->getWorstRating($fullreport->get("X-Xss-Protection")->groupBy('rating')),
        ]);
    }

    /**
     * Returns the worst rating found in a collection.
     *
     * @param Collection $ratingCollection
     * @return string
     */
    protected function getWorstRating(Collection $ratingCollection) {
        $rating = '';
        if($ratingCollection->has('A++'))     $rating = 'A++';
        if($ratingCollection->has('A+'))      $rating = 'A+';
        if($ratingCollection->has('A'))       $rating = 'A';
        if($ratingCollection->has('B++'))     $rating = 'B++';
        if($ratingCollection->has('B+'))      $rating = 'B+';
        if($ratingCollection->has('B'))       $rating = 'B';
        if($ratingCollection->has('C++'))     $rating = 'C++';
        if($ratingCollection->has('C+'))      $rating = 'C+';
        if($ratingCollection->has('C'))       $rating = 'C';
        return $rating;
    }
}