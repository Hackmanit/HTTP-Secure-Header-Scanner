<?php

namespace Tests\Unit;

use App\DOMXSSCheck;
use App\HTTPResponse;
use App\Ratings\SinksRating;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class SinksRatingTest extends TestCase
{
    /** @test */
    public function sinksRatingRates0ForNoContent()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);

        $response = new HTTPResponse($this->request, $client);
        $rating = new SinksRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue($rating->hasError);
        $this->assertTrue(collect($rating->errorMessage)->flatten()->contains('NO_CONTENT'));
    }

    /** @test */
    public function sinksRatingRates100IfThereIsNoScriptTagOnThePage()
    {
        $sampleBody = file_get_contents(base_path().'/tests/Unit/example.org.html');
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $response = new HTTPResponse($this->request, $client);
        $rating = new SinksRating($response);

        $this->assertEquals(100, $rating->score);
        $this->assertFalse($rating->hasError);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('NO_SCRIPT_TAGS'));
    }

    /** @test */
    public function sinksRatingDoesNotFindSinksOutsideOfSearchContext()
    {
        $sampleBody = file_get_contents(base_path().'/tests/Unit/hradek.test.html');
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $response = new HTTPResponse($this->request, $client);
        $rating = new SinksRating($response);

        // Sinks total
        $sinks = DOMXSSCheck::hasSinks($sampleBody, true);
        $this->assertEquals(9, $sinks);

        // Sinks in script-Tags
        $this->assertEquals(1, $rating->testDetails->first()['values']['AMOUNT']);
    }
}
