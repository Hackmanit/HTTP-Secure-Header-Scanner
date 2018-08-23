<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\DOMXSSCheck;
use App\HTTPResponse;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use App\Ratings\SourcesRating;
use GuzzleHttp\Handler\MockHandler;

class SourcesRatingTest extends TestCase
{
    /** @test */
    public function sourcesRatingRates0ForNoContent()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new SourcesRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue($rating->hasError);
        $this->assertTrue(collect($rating->errorMessage)->flatten()->contains('DOMXSS_NO_CONTENT'));
    }

    /** @test */
    public function sourcesRatingRates100IfThereIsNoScriptTagOnThePage()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/example.org.html");
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new SourcesRating($response);

        $this->assertEquals(100, $rating->score);
        $this->assertFalse($rating->hasError);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('DOMXSS_NO_SCRIPT_TAGS'));
    }


    /** @test */
    public function sourcesRatingDoesNotFindSourcesOutsideOfSearchContext()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/hradek.test.html");
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new SourcesRating($response);

        // Sources total
        $sources = DOMXSSCheck::hasSources($sampleBody, true);
        $this->assertEquals(4, $sources);

        // Sources in script-Tags
        $this->assertEquals(2, $rating->testDetails->first()['values']['AMOUNT']);
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
