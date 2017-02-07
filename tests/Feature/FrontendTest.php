<?php

namespace Tests\Feature;

use App\Jobs\AnalyzeSite;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FrontendTest extends TestCase
{
    /** @test */
    public function user_can_access_the_frontend()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee("Enter your URL");
    }

    /** @test */
    public function user_sends_a_scan_request_via_the_frontend_and_the_a_new_job_gets_dispatched_to_the_queue() {
        Queue::fake();

        $response = $this->post("/", [
            "url" => "https://www.hackmanit.de",
            "scan" => ["anchor"]
        ]);

        Queue::assertPushed(AnalyzeSite::class);
    }

    // TODO: Test custom json element-attribute pairs to crawl
}
