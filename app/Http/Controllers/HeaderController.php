<?php

namespace App\Http\Controllers;

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

        $hostIp = exec("/sbin/ip route|awk '/default/ { print $3 }'");

        return view('enter')->with('hostIp', $hostIp);
    }

    /**
     * Main function for the routing.
     *
     * @param Request $request
     * @return array
     */
    public function requestReport(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'proxy' => 'boolean',
            'proxyAddress' => 'required_with:proxy',
            'ignoreTLS' => 'boolean',
        ]);

        if ($validator->fails()) {
            return ["status" => "error", "errors" => $validator->messages()];
        }

        $url = $request->input('url');
        $whiteList = collect(strtolower(parse_url($url, PHP_URL_HOST)));

        $options = collect([]);
        if ($request->input('proxy'))
            $options->put('proxy', $request->input('proxyAddress'));
        if ($request->input('ignoreTLS'))
            $options->push('ignoreTLS');


        $id = str_random();
        $this->dispatch(new AnalyzeSite($id, $url, $whiteList, $options));

        return redirect()->route('displayReport', $id);
    }

    public function displayReport($id) {
        return $id . '<br><a href="/">Back</a>';
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