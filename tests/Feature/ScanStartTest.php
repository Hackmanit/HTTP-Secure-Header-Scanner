<?php

namespace Tests\Feature;

use App\Jobs\DomxssScanJob;
use App\Jobs\HeaderScanJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use TiMacDonald\Log\LogFake;

class ScanStartTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Log::swap(new LogFake());
    }

    /** @test */
    public function a_header_scan_can_be_started_if_the_url_is_given()
    {
        $response = $this->json('POST', '/api/v1/header', [
            'url'          => 'https://testdomain.test',
        ]);

        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Scanning the following URL: https://testdomain.test';
        });

        $response->assertStatus(200);
    }

    /** @test */
    public function a_headerScanJob_can_be_dispatched_if_the_callbackurl_parameter_is_given()
    {
        Queue::fake();

        $response = $this->json('POST', '/api/v1/header', [
            'url'          => 'https://testdomain.test',
            'dangerLevel'  => 0,
            'callbackurls' => ['http://localhost:9002'],
        ]);

        Queue::assertPushed(HeaderScanJob::class, 1);
    }

    /** @test */
    public function a_domxss_scan_can_be_started_if_the_url_is_given()
    {
        $response = $this->json('POST', '/api/v1/domxss', [
            'url'          => 'https://testdomain.test',
        ]);

        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Scanning the following URL: https://testdomain.test';
        });

        $response->assertStatus(200);
    }

    /** @test */
    public function a_domxssScanJob_can_be_dispatched_if_the_callbackurl_parameter_is_given()
    {
        Queue::fake();

        $response = $this->json('POST', '/api/v1/domxss', [
            'url'          => 'https://testdomain.test',
            'dangerLevel'  => 0,
            'callbackurls' => ['http://localhost:9002'],
        ]);

        Queue::assertPushed(DomxssScanJob::class, 1);
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
    public function a_domain_with_umlauts_can_be_scanned()
    {
        $response = $this->json('POST', '/api/v1/header', [
            'url'          => 'https://hää.de',
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
