<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Support\Facades\Log;

class ScanStartTest extends TestCase
{

    public function setUp() {
        parent::setUp();

        $this->app->configureMonologUsing((function ($monolog) {
            $monolog->pushHandler(new \Monolog\Handler\TestHandler());
        }));
    }

    /** @test */
    public function a_header_scan_can_be_started_if_the_correct_parameters_are_sent() {
        $response = $this->json('POST', '/api/v1/header', [
            "url" => "https://siwecos.de",
            "dangerLevel" => 0,
            "callbackurls" => ["http://localhost:9002"]
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function a_domxss_scan_can_be_started_if_the_correct_parameters_are_sent()
    {
        $response = $this->json('POST', '/api/v1/domxss', [
            "url" => "https://siwecos.de",
            "dangerLevel" => 0,
            "callbackurls" => ["http://localhost:9002"]
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function a_scan_can_not_be_started_if_no_parameters_are_sent()
    {
        $response = $this->json('POST', '/api/v1/header', []);
        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v1/domxss', []);
        $response->assertStatus(422);
    }

    /** @test */
    public function a_scan_can_not_be_started_if_invalid_parameters_are_sent()
    {
        $response = $this->json('POST', '/api/v1/header', [
            'url' => 3,
            'dangerLevel' => 0,
            "callbackurls" => ["http://localhost:9002"]
        ]);
        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v1/domxss', [
            'url' => 'https://siwecos.de',
            'dangerLevel' => 100,
            "callbackurls" => ["http://localhost:9002"]
        ]);
        $response->assertStatus(422);
    }

    /** @test */
    public function if_a_callbackurl_is_not_reachable_it_will_be_logged()
    {

        $response = $this->json('POST', '/api/v1/header', [
            'url' => 'https://siwecos.de',
            'dangerLevel' => 0,
            "callbackurls" => ["http://localhost:9002"]
        ]);

        // Retrieve the records from the Monolog TestHandler
        $records = \Log::getMonolog()->getHandlers()[0]->getRecords();

        $this->assertEquals(
            'Could not send the report to the following callback url: http://localhost:9002',
            $records[0]['message']
        );
        // \Log::shouldReceive('warning')->once();


        $response->assertStatus(200);
    }

}
