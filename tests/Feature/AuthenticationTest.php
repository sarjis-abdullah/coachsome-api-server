<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->post('/api/auth/login', ['email' => 'support@tikweb.dk', 'password' => 'D123456']);
        $response->assertStatus(200)->assertJsonStructure(['status', 'user', 'access_token']);
    }
}
