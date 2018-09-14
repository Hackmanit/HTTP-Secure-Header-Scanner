<?php

namespace App\Http\Controllers;

use App\DOMXSSCheck;
use App\HeaderCheck;
use App\Http\Requests\ScanStartRequest;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function headerReport(ScanStartRequest $request)
    {
        $report = (new HeaderCheck($request->json('url')))->report();

        if ($request->json('callbackurls')) {
            $this->notifyCallbacks($request->json('callbackurls'), $report);
        }

        return json_encode($report);
    }

    public function domxssReport(ScanStartRequest $request)
    {
        $report = (new DOMXSSCheck($request->json('url')))->report();

        if ($request->json('callbackurls')) {
            $this->notifyCallbacks($request->json('callbackurls'), $report);
        }

        return json_encode($report);
    }

    protected function notifyCallbacks(array $callbackurls, $report)
    {
        foreach ($callbackurls as $url) {
            try {
                $client = new Client();
                $client->post($url, [
                    'http_errors' => false,
                    'timeout'     => 60,
                    'json'        => $report,
                ]);
            } catch (\Exception $e) {
                Log::warning('Could not send the report to the following callback url: '.$url);
            }
        }
    }
}
