<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Jobs\AnalyzeSite;
use Illuminate\Support\Facades\Redis;

class HeaderController extends Controller
{

    public function index() {
        return view('enter');
    }

    /**
     * Main function for the routing.
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

        $options->put('limit', $request->input('limit'));

        $id = str_random();

        $this->dispatch(new AnalyzeSite($id, $url, $whiteList, $options));

        return redirect()->route('displayReport', $id);
    }

    public function displayReport($id) {
        sleep(5);
        $string = "";
        $count = 1;
        foreach (unserialize(Redis::hget($id, "crawledUrls")) as $link)
            $string .= $count++ . ' | ' . $link . "<br>";

        return  $string . '<br><br><a href="/">Back</a>';
    }

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
}