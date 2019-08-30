<?php

namespace Tests\Unit;

use App\HTTPResponse;
use App\Ratings\CSPRating;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

/**
 * CSPRating is not good. There are many ways to bypass this "secure" rating.
 * TODO: Improve parsing and rating of CSP.
 *
 * Class CSPRatingTest
 */
class CSPRatingTest extends TestCase
{
    /** @test */
    public function cspRating_rates_0_because_header_is_not_set()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new CSPRating($response);

        $this->assertEquals(0, $rating->score);
        $expected = [
            'translationStringId' => 'HEADER_NOT_SET',
            'placeholders' => null,
        ];
        $this->assertEquals($expected, $rating->errorMessage);
    }

    /** @test */
    public function cspRating_rates_50_because_header_is_set_with_unsafe_inline()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Content-Security-Policy' => "default-src 'none'; script-src 'unsafe-inline'; object-src 'none';",
            ]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new CSPRating($response);

        $this->assertEquals(50, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('CSP_UNSAFE_INCLUDED'));
    }

    /** @test */
    public function cspRating_rates_50_because_header_is_set_with_unsafe_eval()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Content-Security-Policy' => "default-src 'none'; script-src 'unsafe-eval'; object-src 'none';",
            ]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new CSPRating($response);

        $this->assertEquals(50, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('CSP_UNSAFE_INCLUDED'));
    }

    /** @test */
    public function cspRating_rates_0_because_header_is_set_without_unsafes_but_without_default_src()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Content-Security-Policy' => "img-src 'self';",
            ]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new CSPRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('CSP_DEFAULT_SRC_MISSING'));
    }

    /** @test */
    public function cspRating_rates_100_because_header_is_set_without_unsafes_and_with_default_src_none()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Content-Security-Policy' => "default-src 'none';",
            ]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new CSPRating($response);

        $this->assertEquals(100, $rating->score);
    }

    /** @test */
    public function cspRating_adds_comment_for_legacy_header()
    {
        // X-Content-Security-Policy
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['X-Content-Security-Policy' => "default-src 'none';"]),
            new Response(200, ['X-WebKit-CSP' => "default-src 'none';"]),
            new Response(200, ['X-Content-Security-Policy' => "default-src 'none';", 'X-WebKit-CSP' => "default-src 'none';"]),
        ]);
        // Finds only X-Content-Security-Policy
        $rating = new CSPRating(new HTTPResponse($this->request, $client));
        $this->assertTrue($rating->testDetails->flatten()->contains('CSP_LEGACY_HEADER_SET'));

        // Finds only X-WebKit-CSP
        $rating = new CSPRating(new HTTPResponse($this->request, $client));
        $this->assertTrue($rating->testDetails->flatten()->contains('CSP_LEGACY_HEADER_SET'));

        // Finds both legacy headers.
        $rating = new CSPRating(new HTTPResponse($this->request, $client));
        $this->assertTrue($rating->testDetails->contains(['translationStringId' => 'CSP_LEGACY_HEADER_SET', 'placeholders' => ['HEADER_NAME' => 'X-Content-Security-Policy']]));
        $this->assertTrue($rating->testDetails->contains(['translationStringId' => 'CSP_LEGACY_HEADER_SET', 'placeholders' => ['HEADER_NAME' => 'X-WebKit-CSP']]));
    }

    /** @test */
    public function cspRating_can_handle_whitespaces()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Content-Security-Policy' => "default-src   'none';",
            ]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new CSPRating($response);

        $this->assertEquals(100, $rating->score);
    }

    /** @test */
    public function CSPRating_detects_wrong_encoding()
    {
        $client = $this->getMockedGuzzleClient([
            // Producing an encoding error
            new Response(200, ['Content-Security-Policy' => zlib_encode('SGVsbG8gV29ybGQ=', ZLIB_ENCODING_RAW)]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new CSPRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->errorMessage)->contains('HEADER_ENCODING_ERROR'));
        $this->assertTrue($rating->hasError);
    }

    /** @test */
    public function cspRating_rates_100_because_header_is_set_without_unsafes_and_with_default_src_self()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Content-Security-Policy' => "default-src 'self';",
            ]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new CSPRating($response);

        $this->assertEquals(100, $rating->score);
    }

    /** @test */
    public function cspRating_rates_0_if_the_policy_is_not_valid()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Content-Security-Policy' => "#default-src 'self'; font-src 'self'",
            ]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new CSPRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->errorMessage)->contains('CSP_IS_NOT_VALID'));
        $this->assertTrue($rating->hasError);
    }
}
