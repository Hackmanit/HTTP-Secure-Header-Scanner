<?php

namespace Tests\Unit;

use App\HTTPResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;

use Tests\TestCase;

class HTTPResponseTest extends TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }

    /** @test */
    public function a_http_response_has_an_url()
    {
        $response = $this->getMockedHTTPResponse([
            new Response(200),
        ]);
        $this->assertEquals("http://testdomain", $response->url());
    }

    /** @test */
    public function a_custom_mock_handler_can_be_used_within_the_HTTPResponse_class()
    {
        $response = $this->getMockedHTTPResponse([
            new Response(200),
        ]);

        $this->assertEquals(200, $response->statusCode());
    }

    /** @test */
    public function the_http_client_follow_redirects()
    {
        $response = $this->getMockedHTTPResponse([
           new Response(301, [
               'Location' => 'http://followMe',
           ]),
           new Response(200, [
               'Strict-Transport-Security' => 'max-age=60; includeSubDomains',
               'X-Content-Type-Options' => 'nosniff',
           ])
       ]);

        $this->assertEquals(200, $response->statusCode());
    }

    /** @test */
    public function the_HTTPResponse_class_returns_the_correct_headers()
    {
        $response = $this->getMockedHTTPResponse([
            new Response(200, [
                'Strict-Transport-Security' => 'max-age=60; includeSubDomains',
            ])
        ]);

        $header = $response->header("strict-transport-security");
        $this->assertCount(1, $header);
        $this->assertEquals("max-age=60; includeSubDomains", $header[0]);
    }

    /** @test */
    public function the_HTTPResponse_class_returns_the_correct_headers_case_insensitive()
    {
        $response = $this->getMockedHTTPResponse([
            new Response(200, ['X-XSS-PROTECTION' => '1; mode=block']),
            new Response(200, ['X-Xss-Protection' => '1; mode=block']),
            new Response(200, ['x-xss-protection' => '1; mode=block']),
        ]);

        $header = $response->header("X-XSS-PROTECTION");
        $this->assertEquals("1; mode=block", $header[0]);

        $header = $response->header("X-Xss-Protection");
        $this->assertEquals("1; mode=block", $header[0]);

        $header = $response->header("x-xss-protection");
        $this->assertEquals("1; mode=block", $header[0]);
    }

    /** @test */
    public function the_HTTPResponse_class_delivers_the_correct_site_body()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/example.org.html");
        $response = $this->getMockedHTTPResponse([
            new Response(200, ['X-XSS-PROTECTION' => '1; mode=block'], $sampleBody)
        ]);

        $this->assertEquals($sampleBody, $response->body());
    }

    /** @test */
    public function the_HTTPResponse_class_delivers_the_correct_site_body_after_a_redirect()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/example.org.html");
        $response = $this->getMockedHTTPResponse([
            new Response(301, ['Location' => 'http://followMe']),
            new Response(200, ['X-XSS-PROTECTION' => '1; mode=block'], $sampleBody)
        ]);

        $this->assertEquals($sampleBody, $response->body());
    }

    /**
     * This method sets and activates the GuzzleHttp Mocking functionality.
     * @param array $responses
     * @return HTTPResponse
     */
    protected function getMockedHTTPResponse(array $responses)
    {
        return new HTTPResponse("http://testdomain", $this->getMockedGuzzleClient($responses));
    }
}
