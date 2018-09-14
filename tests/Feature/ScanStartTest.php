<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ScanStartTest extends TestCase
{
    /** @test */
    public function a_header_scan_can_be_started_if_the_correct_parameters_are_sent()
    {
        $response = $this->json('POST', '/api/v1/header', [
            'url'          => 'https://siwecos.de',
            'dangerLevel'  => 0,
            'callbackurls' => ['http://localhost:9002'],
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function a_domxss_scan_can_be_started_if_the_correct_parameters_are_sent()
    {
        $response = $this->json('POST', '/api/v1/domxss', [
            'url'          => 'https://siwecos.de',
            'dangerLevel'  => 0,
            'callbackurls' => ['http://localhost:9002'],
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function the_callbackurl_and_dangerLevel_parameters_are_optional()
    {
        $response = $this->json('POST', '/api/v1/domxss', [
            'url' => 'https://siwecos.de',
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
            'url'          => 3,
            'dangerLevel'  => 0,
            'callbackurls' => ['http://localhost:9002'],
        ]);
        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v1/domxss', [
            'url'          => 'https://siwecos.de',
            'dangerLevel'  => 100,
            'callbackurls' => ['http://localhost:9002'],
        ]);
        $response->assertStatus(422);
    }

    /** @test */
    public function if_a_callbackurl_is_not_reachable_it_will_be_logged()
    {
        Log::shouldReceive('warning')
            ->with('Could not send the report to the following callback url: http://localhost:9002')
            ->once();

        $response = $this->json('POST', '/api/v1/header', [
            'url'          => 'https://siwecos.de',
            'dangerLevel'  => 0,
            'callbackurls' => ['http://localhost:9002'],
        ]);

        $response->assertStatus(200);
    }
}
