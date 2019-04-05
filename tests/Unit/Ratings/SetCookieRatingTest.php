<?php

namespace Tests\Unit;

use App\HTTPResponse;
use App\Ratings\SetCookieRating;
use Delight\Cookie\Cookie;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class SetCookieRatingTest extends TestCase
{
    /** @test */
    public function setCookieRating_is_hidden_for_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);

        $response = new HTTPResponse($this->request, $client);
        $rating = new SetCookieRating($response);

        $this->assertEquals('hidden', $rating->scoreType);
    }

    /** @test */
    public function setCookieRating_detects_wrong_encoding()
    {
        $client = $this->getMockedGuzzleClient([
            // Producing an encoding error
            new Response(200, ['Set-Cookie' => zlib_encode('SGVsbG8gV29ybGQ=', ZLIB_ENCODING_RAW)]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new SetCookieRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->errorMessage)->contains('HEADER_ENCODING_ERROR'));
        $this->assertTrue($rating->hasError);
    }

    /** @test */
    public function setCookieRating_detects_rates_0_if_no_security_flags_are_set()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Set-Cookie' => 'session=myCookie; Keks;']),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new SetCookieRating($response);

        $this->assertFalse($rating->hasError);
        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('NO_HTTPONLY_FLAG_SET'));
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('NO_SECURE_FLAG_SET'));
    }

    /** @test */
    public function setCookieRating_detects_secure_flag()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Set-Cookie' => 'session=myCookie; Secure']),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new SetCookieRating($response);

        $this->assertFalse($rating->hasError);
        $this->assertEquals(90, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('SECURE_FLAG_SET'));
    }

    /** @test */
    public function setCookieRating_detects_httpOnly_flag()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Set-Cookie' => 'session=myCookie; HttpOnly']),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new SetCookieRating($response);

        $this->assertFalse($rating->hasError);
        $this->assertEquals(10, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('HTTPONLY_FLAG_SET'));
    }

    /** @test */
    public function setCookieRating_rates_100_if_secure_and_httpOnly_flags_are_set()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Set-Cookie' => 'session=myCookie; HttpOnly; secure']),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new SetCookieRating($response);

        $this->assertFalse($rating->hasError);
        $this->assertEquals(100, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('SECURE_FLAG_SET'));
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('HTTPONLY_FLAG_SET'));
    }

    /** @test */
    public function setCookieRating_rates_an_average_score_if_different_cookies_are_set()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Set-Cookie' => ['session=myCookie; httponly', 'keks=newCookie; SECURE']]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new SetCookieRating($response);

        $this->assertFalse($rating->hasError);
        $this->assertEquals(50, $rating->score);

        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Set-Cookie' => ['session=myCookie; Secure; HttpOnly', 'keks=newCookie; Secure; HttpOnly']]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new SetCookieRating($response);

        $this->assertFalse($rating->hasError);
        $this->assertEquals(100, $rating->score);

        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Set-Cookie' => [
                'session=myCookie; Secure',
                'keks=newCookie; Secure; HttpOnly',
                'kruemel=anotherCookie; HttpOnly',
            ]]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new SetCookieRating($response);

        $this->assertFalse($rating->hasError);
        $this->assertEquals(67, $rating->score);
    }

    /** @test */
    public function setCookieRating_is_type_hidden_and_if_there_are_cookies_type_is_set_to_warning()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, []),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new SetCookieRating($response);

        $this->assertFalse($rating->hasError);
        $this->assertEquals('hidden', $rating->scoreType);

        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Set-Cookie' => 'session=myCookie; httponly']),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new SetCookieRating($response);

        $this->assertFalse($rating->hasError);
        $this->assertEquals('warning', $rating->scoreType);
    }

    /** @test */
    public function a_invalid_SetCookie_header_will_be_catched_and_rated_with_minus5_score()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Set-Cookie' => 'HttpOnly; Secure']),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new SetCookieRating($response);

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals(-5, $rating->score);
    }
}
