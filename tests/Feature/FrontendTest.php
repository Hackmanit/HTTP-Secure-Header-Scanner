<?php

namespace Tests\Feature;

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
}
