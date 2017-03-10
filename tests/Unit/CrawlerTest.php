<?php

namespace Tests\Unit;

use App\Crawler;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\LaravelCacheStorage;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Mockery;
use ReflectionClass;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CrawlerTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }

    /** @test */
    public function parseDom_returns_the_correct_amount_of_anchor_links()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/parseDom-optimizeLinks.html");
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $crawler = new Crawler("testID", "http://testdomain", collect(), collect(['anchor']), $client);
        $parseDom = new \ReflectionMethod(Crawler::class, "parseDom");
        $parseDom->setAccessible(true);

        $this->assertEquals(8, $parseDom->invoke($crawler, "http://testdomain")->count());
    }

    /** @test */
    public function parseDom_returns_the_correct_amount_of_image_links()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/parseDom-optimizeLinks.html");
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $crawler = new Crawler("testID", "http://testdomain", collect(), collect(['image']), $client);
        $parseDom = new \ReflectionMethod(Crawler::class, "parseDom");
        $parseDom->setAccessible(true);

        $this->assertEquals(4, $parseDom->invoke($crawler, "http://testdomain")->count());
    }

    /** @test */
    public function parseDom_returns_the_correct_amount_of_area_links()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/parseDom-optimizeLinks.html");
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $crawler = new Crawler("testID", "http://testdomain", collect(), collect(['area']), $client);
        $parseDom = new \ReflectionMethod(Crawler::class, "parseDom");
        $parseDom->setAccessible(true);
        $links = $parseDom->invoke($crawler, "http://testdomain");

        $this->assertEquals(15, $links->count());
    }

    /** @test */
    public function parseDom_returns_the_correct_amount_of_media_links()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/parseDom-optimizeLinks.html");
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $crawler = new Crawler("testID", "http://testdomain", collect(), collect(['media']), $client);
        $parseDom = new \ReflectionMethod(Crawler::class, "parseDom");
        $parseDom->setAccessible(true);
        $links = $parseDom->invoke($crawler, "http://testdomain");

        $this->assertEquals(8, $links->count()); // 6 links but 2 boolean values caused by the empty video and audio tags
    }

    /** @test */
    public function parseDom_returns_the_correct_amount_of_script_links()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/parseDom-optimizeLinks.html");
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $crawler = new Crawler("testID", "http://testdomain", collect(), collect(['script']), $client);
        $parseDom = new \ReflectionMethod(Crawler::class, "parseDom");
        $parseDom->setAccessible(true);
        $links = $parseDom->invoke($crawler, "http://testdomain");

        $this->assertEquals(3, $links->count());
    }

    /** @test */
    public function parseDom_returns_the_correct_amount_of_frame_links()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/parseDom-optimizeLinks.html");
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $crawler = new Crawler("testID", "http://testdomain", collect(), collect(['frame']), $client);
        $parseDom = new \ReflectionMethod(Crawler::class, "parseDom");
        $parseDom->setAccessible(true);
        $links = $parseDom->invoke($crawler, "http://testdomain");

        $this->assertEquals(4, $links->count());
    }

    /** @test */
    public function parse_dom_gets_all_links_on_one_site()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/parseDom-optimizeLinks.html");
        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $crawler = new Crawler("testID", "http://testdomain", collect(), collect(['anchor', 'image', 'area', 'media', 'script', 'frame']), $client);
        $parseDom = new \ReflectionMethod(Crawler::class, "parseDom");
        $parseDom->setAccessible(true);
        $links = $parseDom->invoke($crawler, "http://testdomain");

        $this->assertEquals(42, $links->count());
    }

    /** @test */
    public function optimizeUrls_works_as_expected()
    {
        $links = $this->getOptimizedUrls(collect([ "index.html" ]));
        $this->assertEquals("http://testdomain/index.html", $links->first());

        // one in, one out - no crap
        $this->assertCount(1, $links);

        $links = $this->getOptimizedUrls(collect([ "./impress.php" ]));
        $this->assertEquals("http://testdomain/impress.php", $links->first());

        $links = $this->getOptimizedUrls(collect([ "query.php?key=value" ]));
        $this->assertEquals("http://testdomain/query.php?key=value", $links->first());

        $links = $this->getOptimizedUrls(collect([ 1234 ]));
        $this->assertEquals("http://testdomain/1234", $links->first());

        $links = $this->getOptimizedUrls(collect([ "http://testdomain/absolute/site" ]));
        $this->assertEquals("http://testdomain/absolute/site", $links->first());

        $links = $this->getOptimizedUrls(collect([ "http://testdomain:8080/withPort.html" ]));
        $this->assertEquals("http://testdomain:8080/withPort.html", $links->first());

        $links = $this->getOptimizedUrls(collect([ "https://testdomain/secureScheme" ]));
        $this->assertEquals("https://testdomain/secureScheme", $links->first());

        $links = $this->getOptimizedUrls(collect([ "https://TESTDOMAIN/" ]));
        $this->assertEquals("https://testdomain/", $links->first());

        // The following should not be parsed and return an empty collection
        $links = $this->getOptimizedUrls(collect([ "http://not-on-whitelist.org/site.php" ]));
        $this->assertCount(0, $links);

        $links = $this->getOptimizedUrls(collect([ "mailto:code@lednerb.de" ]));
        $this->assertCount(0, $links);

        $links = $this->getOptimizedUrls(collect([ "javascript:alert(1)" ]));
        $this->assertCount(0, $links);

        $links = $this->getOptimizedUrls(collect([ "data:something" ]));
        $this->assertCount(0, $links);
    }

    /** @test */
    public function crawler_crawls_all_links_on_one_site_and_parses_them_correctly()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/parseDom-optimizeLinks.html");
        $client = $this->getMockedGuzzleClient([
            // Delivers 2 responses so the crawler can follow
            new Response(200, [], $sampleBody),
            new Response(200, [], $sampleBody),
        ]);

        $crawler = new Crawler('testId', 'https://testdomain', collect([ 'www.testdomain' ]), collect(['anchor', "image", "area", "media", "script", "frame"]), $client);
        $links = $crawler->extractLinks("http://testdomain");

        $this->assertCount(38, $links);
    }

    /** @test
     * LIVE TEST --- just fix the annotation
     */
    public function crawler_crawls_the_live_hackmanit_site_and_gets_more_than_10_anchor_links()
    {
        //
        $handler = HandlerStack::create();
        $handler->push(
            new CacheMiddleware(
                new PrivateCacheStrategy(
                    new LaravelCacheStorage(
                        Cache::store('redis')
                    )
                )
            ),
            'cache'
        );
        $client = new Client( ["handler" => $handler] );
        $crawler = new Crawler('hackmanitID', 'https://www.hackmanit.de', collect(['hackmanit.de']), collect(['anchor', 'limit' => '30']), $client);

        $links = $crawler->extractAllLinks();

        $this->assertGreaterThanOrEqual(27, $links->count());
    }


    /**
     * This method sets and activates the GuzzleHttp Mocking functionality.
     * @param array $responses
     * @return Client
     */
    protected function getMockedGuzzleClient(array $responses) {
        $mock = new MockHandler( $responses );
        $handler = HandlerStack::create( $mock );
        return (new Client( ["handler" => $handler] )) ;
    }

    /**
     * Returns the optimized links for smaller testing methods.
     * @param Collection $links
     * @return mixed
     */
    protected function getOptimizedUrls(Collection $links) {
        $crawler = new Crawler("testID", "http://testdomain", collect(), collect());
        $optimizeUrls = new \ReflectionMethod(Crawler::class, "optimizeUrls");
        $optimizeUrls->setAccessible(true);

        return $optimizeUrls->invokeArgs($crawler, [ "http://testdomain", $links ]);
    }

    protected static function getMethod($class, $method) {
        $class = new ReflectionClass($class);
        $return = $class->getMethod($method);
        $return->setAccessible(true);
        return $return;
    }
}
