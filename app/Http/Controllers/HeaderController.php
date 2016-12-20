<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Jobs\AnalyzeSite;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Validator;
use Response;
use Illuminate\Support\Facades\Redis;
use App\Report;

class HeaderController extends Controller
{

    public function index() {
        return view('enter');
    }

    /**
     * Main function for the routing.
     *
     * @param Request $request
     * @return array
     */
    public function requestReport(ReportRequest $request)
    {
        $url = $request->input('url');
        if (substr($url, -1) !== '/')
            $url = $url . '/';

        // whitelist
        $whiteList = collect(strtolower(parse_url($url, PHP_URL_HOST)));
        $whiteList->push(explode("\n", $request->input('whitelist')))->flatten();

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

        $id = str_random();
        $this->dispatch(new AnalyzeSite($id, $url, $whiteList, $options, $request->input('limit')));

        return redirect()->route('displayReport', $id);
    }

    public function displayReport($id) {
        $string = "";
        $count = 1;
        foreach (unserialize(Redis::hget($id, "crawledUrls")) as $link)
            $string .= $count++ . ' | ' . $link . "<br>";

        return  $string . '<br><br><a href="/">Back</a>';
    }

    public function jsConfig() {
        return [
            'LIMIT' => env("LIMIT"),
            'HOST_IP' => exec("/sbin/ip route|awk '/default/ { print $3 }'")
        ];
    }



    /**
     * Creates the report and handles caching.
     *
     * @param $url
     * @param Response $response
     * @return Report|array
     * @internal param $GuzzleHttp/Psr7/Response $response
     */
    protected function makeReport($url, Response $response)
    {
        $report = new Report($response);
        $report = [
            "status" => "success",
            "nonce" => str_random(),
            "scores" => $report->toJson(),
            "headers" => $response->getHeaders(),
            "url" => $url,
            "date" => date("Y-m-d H:i:s")
        ];

        Redis::set("report-" . $url, serialize($report));
        Redis::expire("report-" . $url, env("HOST_CACHE", 10));


        return $report;
    }

    protected function generateReports(Collection $links) {
        $reports = collect();
        foreach ($links as $link)
            $reports->push($this->makeReport($link, $this->getHttpResponse($link)));

        return $reports;
    }



}