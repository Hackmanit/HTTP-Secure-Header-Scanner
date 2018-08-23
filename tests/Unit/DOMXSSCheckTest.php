<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\DOMXSSCheck;
use App\HTTPResponse;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;


class DOMXSSCheckTest extends TestCase
{
    /** @test */
    public function domxssCheckFindsSinks()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/hradek.test.html");

        $this->assertTrue(DOMXSSCheck::hasSinks($sampleBody));
    }

    /** @test */
    public function domxssCheckFindsSources()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/hradek.test.html");

        $this->assertTrue(DOMXSSCheck::hasSources($sampleBody));
    }


    /**
     * This method sets and activates the GuzzleHttp Mocking functionality.
     * @param array $responses
     * @return Client
     */
    protected function getMockedGuzzleClient(array $responses)
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        return (new Client(["handler" => $handler]));
    }
}
