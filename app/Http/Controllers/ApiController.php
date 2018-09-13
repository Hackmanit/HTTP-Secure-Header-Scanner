<?php

namespace App\Http\Controllers;

use App\HeaderCheck;
use App\DOMXSSCheck;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ScanStartRequest;

class ApiController extends Controller
{

    public function headerReport(ScanStartRequest $request) {
        $check = new HeaderCheck($request->json('url'));

        $this->notifyCallbacks($request->json('callbackurls'), $check);

        return "OK";
    }

    public function domxssReport(ScanStartRequest $request){
        $check = new DOMXSSCheck($request->json('url'));

        $this->notifyCallbacks($request->json('callbackurls'), $check);

        return "OK";
    }

    protected function notifyCallbacks(array $callbackurls, $check) {
        $report = $check->report();
        foreach ($callbackurls as $url) {
            try {
                $client = new Client();
                $client->post($url, [
                    'http_errors' => false,
                    'timeout' => 60,
                    'json' => $report
                ]);
            }
            catch (\Exception $e) {
                Log::warning("Could not send the report to the following callback url: " . $url);
            }
        }
    }

}
