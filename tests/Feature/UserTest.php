<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function authenticate($isAdmin = false)
    {
        $email = $isAdmin ? 'admin@mail.com' : 'user@mail.com';
        $user = User::where('email', $email)->first();
        $token = JWTAuth::fromUser($user);
        return [$user, $token];
    }

    public function test_list_users_success()
    {
        list($user, $token) = $this->authenticate();
        User::factory()->count(3)->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'is_admin']
            ],
            'meta'
        ]);
    }

    public function test_list_users_unauthorized()
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
    }

    public function test_get_user_by_id_success()
    {
        list($user, $token) = $this->authenticate();
        $targetUser = User::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/users/' . $targetUser->id);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $targetUser->id
            ]
        ]);
    }

    public function test_get_user_by_id_not_found()
    {
        list($user, $token) = $this->authenticate();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/users/9999');

        $response->assertStatus(404);
    }

    public function test_create_user_success()
    {
        list($admin, $token) = $this->authenticate(true);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/users', $userData);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'name' => 'New User',
                'email' => 'newuser@example.com'
            ]
        ]);
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_create_user_forbidden_for_non_admin()
    {
        list($user, $token) = $this->authenticate(false);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/users', [
                'name' => 'Should Fail',
                'email' => 'fail@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(403);
    }

    public function test_create_user_validation_failed()
    {
        list($admin, $token) = $this->authenticate(true);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/users', []);

        $response->assertStatus(400);
        $response->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_create_user_duplicate_email()
    {
        list($admin, $token) = $this->authenticate(true);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'user@mail.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(400);
    }

    public function test_update_user_success()
    {
        list($admin, $token) = $this->authenticate(true);
        $targetUser = User::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson('/api/users/' . $targetUser->id, [
                'name' => 'Updated Name',
                'is_admin' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'name' => 'Updated Name',
                'is_admin' => true
            ]
        ]);
    }

    public function test_update_user_not_found()
    {
        list($admin, $token) = $this->authenticate(true);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson('/api/users/9999', ['name' => 'Non Existent']);

        $response->assertStatus(404);
    }

    public function test_update_user_forbidden_for_non_admin()
    {
        list($user, $token) = $this->authenticate(false);
        $targetUser = User::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson('/api/users/' . $targetUser->id, ['name' => 'Failed Update']);

        $response->assertStatus(403);
    }

    public function test_delete_user_success()
    {
        list($admin, $token) = $this->authenticate(true);
        $targetUser = User::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/users/' . $targetUser->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_delete_user_not_found()
    {
        list($admin, $token) = $this->authenticate(true);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/users/9999');

        $response->assertStatus(404);
    }

    public function test_delete_user_forbidden_for_non_admin()
    {
        list($user, $token) = $this->authenticate(false);
        $targetUser = User::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/users/' . $targetUser->id);

        $response->assertStatus(403);
    }
}
