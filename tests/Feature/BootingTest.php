<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class BootingTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBootingApi()
    {
        $response = $this->get("/api/booting");
        $response->assertStatus(200);
    }
}
