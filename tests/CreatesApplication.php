<?php

namespace Tests;

use App\Http\Requests\ScanStartRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function setUp()
    {
        $this->request = new ScanStartRequest(['url' => 'https://testdomain']);
        parent::setUp();
    }

    /**
     * This method sets and activates the GuzzleHttp Mocking functionality.
     *
     * @param array $responses
     *
     * @return Client
     */
    protected function getMockedGuzzleClient(array $responses)
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);

        return new Client(['handler' => $handler]);
    }
}
