<?php

namespace Tests\Unit;

use App\HTTPResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Tests\TestCase;


class HTTPResponseTest extends TestCase
{

    protected $response = null;

    public function tearDown()
    {
        Mockery::close();
        $this->response = null;
    }

    /** @test */
    public function a_http_response_has_an_url()
    {
        $this->response = new HTTPResponse( "http://testdomain" );

        $this->assertEquals( "http://testdomain", $this->response->url() );
    }

    /** @test */
    public function a_custom_mock_handler_can_be_used_within_the_HTTPResponse_class()
    {
        $mock = new MockHandler( [
            new Response( 200, [
                'Strict-Transport-Security' => 'max-age=60; includeSubDomains',
                'X-Contnent-Type-Options' => 'nosniff',
            ] )
        ] );
        $this->getHTTPResponseMockForTesting($mock);

        $this->assertEquals( 200, $this->response->statusCode() );
    }

    /** @test */
    public function the_http_client_follow_redirects()
    {
        $mock = new MockHandler( [
            new Response( 301, [
                'Location' => 'http://followMe',
            ] ),
            new Response( 200, [
                'Strict-Transport-Security' => 'max-age=60; includeSubDomains',
                'X-Contnent-Type-Options' => 'nosniff',
            ] )
        ] );
        $this->getHTTPResponseMockForTesting($mock);

        $this->assertEquals( 200, $this->response->statusCode() );
    }

    /** @test */
    public function the_HTTPResponse_class_returns_the_correct_headers() {
        $mock = new MockHandler( [
            new Response( 200, [
                'Strict-Transport-Security' => 'max-age=60; includeSubDomains',
            ] )
        ] );
        $this->getHTTPResponseMockForTesting($mock);

        $header = $this->response->header("strict-transport-security");
        $this->assertCount(1, $header);
        $this->assertEquals("max-age=60; includeSubDomains", $header[0]);
    }

    /**
     * This method sets and activates the GuzzleHttp Mocking functionality.
     * @param $mock MockHandler to setup the expected responses
     */
    protected function getHTTPResponseMockForTesting($mock) {
        $handler = HandlerStack::create( $mock );
        $this->response = Mockery::mock( 'App\HTTPResponse[client]', ["http://testdomain"] );
        $this->response->shouldReceive( "client" )->once()->andReturn( new Client( ["handler" => $handler] ) );
    }


}