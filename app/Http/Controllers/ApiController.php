<?php

namespace App\Http\Controllers;

use App\HeaderCheck;
use App\DomxssCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{

    public function headerReport(Request $request) {

        $this->checkSiwecosRequest($request);

        $check = new HeaderCheck($request->json('url'));

        $this->notifyCallbacks($request->json('callbackurls'), $check);

        return "OK";
    }

    public function domxssReport(Request $request){

        $this->checkSiwecosRequest($request);

        $check = new DomxssCheck($request->json('url'));

        $this->notifyCallbacks($request->json('callbackurls'), $check);

        return "OK";
    }

    protected function checkSiwecosRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'dangerLevel' => 'integer|min:0|max:10',
            'callbackurls' => 'required|array',
            'callbackurls.*' => 'url'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        return true;
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
                Log::debug($e);
                Log::warning("Trying to send an error");
                try {
                    $client = new Client();
                    $client->post($url, [
                        'http_errors' => false,
                        'timeout' => 60,
                        'json' => [
                            "name" => "HEADER",
                            "hasError" => "true",
                            "score" => 0,
                            "errorMessage" => [
                                "placeholder" => "GENERAL_ERROR",
                                "values" => [
                                    "ERRORTEXT" => $e->getMessage()
                                ]
                            ]
                        ]
                    ]);
                } catch (\Exception $e) {
                    Log::critical($e);
                }
            }
        }
    }

}
