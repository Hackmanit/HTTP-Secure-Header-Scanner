<?php

namespace App\Http\Controllers;

use App\DOMXSSCheck;
use App\HeaderCheck;
use App\Http\Requests\ScanStartRequest;
use App\Jobs\DomxssScanJob;
use App\Jobs\HeaderScanJob;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function headerReport(ScanStartRequest $request)
    {
        if ($request->json('callbackurls')) {
            HeaderScanJob::dispatch($request->json('url'), $request->json('callbackurls'));

            return 'OK';
        }

        return json_encode((new HeaderCheck($request->json('url')))->report());
    }

    public function domxssReport(ScanStartRequest $request)
    {
        if ($request->json('callbackurls')) {
            DomxssScanJob::dispatch($request);

            return 'OK';
        }

        return json_encode((new DOMXSSCheck($request->json('url')))->report());
    }

    public static function notifyCallbacks(array $callbackurls, $report)
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
