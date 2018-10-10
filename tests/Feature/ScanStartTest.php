<?php

namespace Tests\Feature;

use Tests\TestCase;
use TiMacDonald\Log\LogFake;
use Illuminate\Support\Facades\Log;

class ScanStartTest extends TestCase
{

    public function setUp() {
        parent::setUp();
        Log::swap(new LogFake);
    }

    /** @test */
    public function a_header_scan_can_be_started_if_the_correct_parameters_are_sent()
    {
        $response = $this->json('POST', '/api/v1/header', [
            'url'          => 'https://testdomain.test',
            'dangerLevel'  => 0,
            'callbackurls' => ['http://localhost:9002'],
        ]);

        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Scanning the following URL: https://testdomain.test';
        });

        $response->assertStatus(200);
    }

    /** @test */
    public function a_domxss_scan_can_be_started_if_the_correct_parameters_are_sent()
    {
        $response = $this->json('POST', '/api/v1/domxss', [
            'url'          => 'https://testdomain.test',
            'dangerLevel'  => 0,
            'callbackurls' => ['http://localhost:9002'],
        ]);

        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Scanning the following URL: https://testdomain.test';
        });

        $response->assertStatus(200);
    }

    /** @test */
    public function the_callbackurl_and_dangerLevel_parameters_are_optional()
    {
        $response = $this->json('POST', '/api/v1/domxss', [
            'url' => 'https://testdomain.test',
        ]);

        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Scanning the following URL: https://testdomain.test';
        });

        $response->assertStatus(200);
    }

    /** @test */
    public function a_scan_can_not_be_started_if_no_parameters_are_sent()
    {
        $response = $this->json('POST', '/api/v1/header', []);
        $response->assertStatus(422);

        Log::assertNotLogged('info', function ($message, $context) {
            return str_contains($message, 'Scanning the following URL: ');
        });

        $response = $this->json('POST', '/api/v1/domxss', []);
        $response->assertStatus(422);

        Log::assertNotLogged('info', function ($message, $context) {
            return str_contains($message, 'Scanning the following URL: ');
        });
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

        Log::assertNotLogged('info', function ($message, $context) {
            return $message === 'Scanning the following URL: https://testdomain.test';
        });


        $response = $this->json('POST', '/api/v1/domxss', [
            'url'          => 'https://testdomain.test',
            'dangerLevel'  => 100,
            'callbackurls' => ['http://localhost:9002'],
        ]);
        $response->assertStatus(422);

        Log::assertNotLogged('info', function ($message, $context) {
            return $message === 'Scanning the following URL: https://testdomain.test';
        });
    }

    /** @test */
    public function if_a_callbackurl_is_not_reachable_it_will_be_logged()
    {
        $response = $this->json('POST', '/api/v1/header', [
            'url'          => 'https://testdomain.test',
            'dangerLevel'  => 0,
            'callbackurls' => ['http://localhost:9002'],
        ]);

        Log::assertLogged('warning', function($message, $context) {
            return $message === 'Could not send the report to the following callback url: http://localhost:9002';
        });

        $response->assertStatus(200);
    }

    /** @test */
    public function a_domain_with_umlauts_can_be_scanned()
    {
        $response = $this->json('POST', '/api/v1/header', [
            'url'          => 'https://hää.de',
            'dangerLevel'  => 0,
            'callbackurls' => ['http://localhost:9002'],
        ]);

        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Scanning the following URL: https://xn--h-0faa.de';
        });

        $response = $this->json('POST', '/api/v1/domxss', [
            'url'          => 'https://hää.de',
            'dangerLevel'  => 0,
            'callbackurls' => ['http://localhost:9002'],
        ]);

        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Scanning the following URL: https://xn--h-0faa.de';
        });
    }
}
