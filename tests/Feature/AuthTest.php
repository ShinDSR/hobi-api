<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_success()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@mail.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'token_type' => 'bearer',
                'user' => [
                    'email' => 'user@mail.com'
                ]
            ]
        ]);
        $this->assertNotNull($response->json('data.access_token'));
    }

    public function test_login_failed_wrong_password()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@mail.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'errors' => 'Email or password is incorrect'
        ]);
    }

    public function test_login_failed_wrong_email()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'errors' => 'Email or password is incorrect'
        ]);
    }

    public function test_login_failed_validation_required()
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(400);
        $response->assertJsonStructure([
            'errors' => ['email', 'password']
        ]);
    }

    public function test_login_failed_validation_email_format()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid-email',
            'password' => 'password',
        ]);

        $response->assertStatus(400);
        $response->assertJsonStructure([
            'errors' => ['email']
        ]);
    }

    public function test_logout_success()
    {
        $user = User::where('email', 'user@mail.com')->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => 'OK'
        ]);
    }

    public function test_logout_unauthorized()
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    public function test_refresh_success()
    {
        $user = User::where('email', 'user@mail.com')->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/refresh');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.access_token'));
    }

    public function test_refresh_unauthorized()
    {
        $response = $this->postJson('/api/auth/refresh');

        $response->assertStatus(401);
    }
}
