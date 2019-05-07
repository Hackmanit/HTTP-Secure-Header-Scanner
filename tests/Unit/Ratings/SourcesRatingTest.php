<?php

namespace Tests\Unit;

use App\DOMXSSCheck;
use App\HTTPResponse;
use App\Ratings\SourcesRating;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class SourcesRatingTest extends TestCase
{
    /** @test */
    public function sourcesRatingRates0ForNoContent()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);

        $response = new HTTPResponse($this->request, $client);
        $rating = new SourcesRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue($rating->hasError);
        $this->assertTrue(collect($rating->errorMessage)->flatten()->contains('NO_CONTENT'));
    }

    /** @test */
    public function sourcesRatingRates100IfThereIsNoScriptTagOnThePage()
    {
        $sampleBody = file_get_contents(base_path() . '/tests/Unit/example.org.html');
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $response = new HTTPResponse($this->request, $client);
        $rating = new SourcesRating($response);

        $this->assertEquals(100, $rating->score);
        $this->assertFalse($rating->hasError);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('NO_SCRIPT_TAGS'));
    }

    /** @test */
    public function sourcesRatingDoesNotFindSourcesOutsideOfSearchContext()
    {
        $sampleBody = file_get_contents(base_path() . '/tests/Unit/hradek.test.html');
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $response = new HTTPResponse($this->request, $client);
        $rating = new SourcesRating($response);

        // Sources total
        $sources = DOMXSSCheck::hasSources($sampleBody, true);
        $this->assertEquals(6, $sources);

        // Sources in script-Tags
        $this->assertEquals(2, $rating->testDetails->first()['placeholders']['AMOUNT']);
    }
}
