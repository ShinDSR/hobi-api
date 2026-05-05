<?php

namespace Tests\Feature;

use App\Models\Hobby;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class HobbyTest extends TestCase
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

    public function test_list_my_hobbies_success()
    {
        list($user, $token) = $this->authenticate();
        $count = Hobby::where('user_id', $user->id)->count();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/hobbies');

        $response->assertStatus(200);
        $response->assertJsonCount($count, 'data');
    }

    public function test_create_hobby_success()
    {
        list($user, $token) = $this->authenticate();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/hobbies', ['name' => 'New Unique Hobby']);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'name' => 'New Unique Hobby'
            ]
        ]);
        $this->assertDatabaseHas('hobbies', ['name' => 'New Unique Hobby', 'user_id' => $user->id]);
    }

    public function test_create_hobby_validation_failed()
    {
        list($user, $token) = $this->authenticate();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/hobbies', []);

        $response->assertStatus(400);
        $response->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_get_hobby_by_id_success()
    {
        list($user, $token) = $this->authenticate();
        $hobby = Hobby::where('user_id', $user->id)->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/hobbies/' . $hobby->id);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $hobby->id
            ]
        ]);
    }

    public function test_get_hobby_by_id_not_found()
    {
        list($user, $token) = $this->authenticate();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/hobbies/9999');

        $response->assertStatus(404);
    }

    public function test_get_other_user_hobby_forbidden()
    {
        list($user, $token) = $this->authenticate();
        $otherUser = User::where('email', 'admin@mail.com')->first();
        $otherHobby = Hobby::where('user_id', $otherUser->id)->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/hobbies/' . $otherHobby->id);

        $response->assertStatus(404); 
    }

    public function test_update_hobby_success()
    {
        list($user, $token) = $this->authenticate();
        $hobby = Hobby::where('user_id', $user->id)->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson('/api/hobbies/' . $hobby->id, ['name' => 'Updated Hobby Name']);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'name' => 'Updated Hobby Name'
            ]
        ]);
    }

    public function test_update_hobby_validation_failed()
    {
        list($user, $token) = $this->authenticate();
        $hobby = Hobby::where('user_id', $user->id)->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson('/api/hobbies/' . $hobby->id, ['name' => '']);

        $response->assertStatus(400);
        $response->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_delete_hobby_success()
    {
        list($user, $token) = $this->authenticate();
        $hobby = Hobby::where('user_id', $user->id)->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/hobbies/' . $hobby->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('hobbies', ['id' => $hobby->id]);
    }

    public function test_delete_other_user_hobby_forbidden()
    {
        list($user, $token) = $this->authenticate();
        $otherUser = User::where('email', 'admin@mail.com')->first();
        $otherHobby = Hobby::where('user_id', $otherUser->id)->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/hobbies/' . $otherHobby->id);

        $response->assertStatus(404);
    }

    public function test_admin_list_user_hobbies_success()
    {
        list($admin, $token) = $this->authenticate(true);
        $targetUser = User::where('email', 'user@mail.com')->first();
        $count = Hobby::where('user_id', $targetUser->id)->count();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/users/{$targetUser->id}/hobbies");

        $response->assertStatus(200);
        $response->assertJsonCount($count, 'data');
    }

    public function test_admin_create_user_hobby_success()
    {
        list($admin, $token) = $this->authenticate(true);
        $targetUser = User::where('email', 'user@mail.com')->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/users/{$targetUser->id}/hobbies", ['name' => 'Admin Created Hobby']);

        $response->assertStatus(201);
        $this->assertDatabaseHas('hobbies', ['name' => 'Admin Created Hobby', 'user_id' => $targetUser->id]);
    }

    public function test_non_admin_cannot_access_user_hobbies()
    {
        list($user, $token) = $this->authenticate(false);
        $targetUser = User::where('email', 'admin@mail.com')->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/users/{$targetUser->id}/hobbies");

        $response->assertStatus(403);
    }

    public function test_admin_update_user_hobby_success()
    {
        list($admin, $token) = $this->authenticate(true);
        $targetUser = User::where('email', 'user@mail.com')->first();
        $hobby = Hobby::where('user_id', $targetUser->id)->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson("/api/users/{$targetUser->id}/hobbies/{$hobby->id}", ['name' => 'Admin Updated Hobby']);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'name' => 'Admin Updated Hobby'
            ]
        ]);
    }

    public function test_admin_update_user_hobby_not_found()
    {
        list($admin, $token) = $this->authenticate(true);
        $targetUser = User::where('email', 'user@mail.com')->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson("/api/users/{$targetUser->id}/hobbies/9999", ['name' => 'Coding']);

        $response->assertStatus(404);
    }

    public function test_admin_delete_user_hobby_success()
    {
        list($admin, $token) = $this->authenticate(true);
        $targetUser = User::where('email', 'user@mail.com')->first();
        $hobby = Hobby::where('user_id', $targetUser->id)->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson("/api/users/{$targetUser->id}/hobbies/{$hobby->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('hobbies', ['id' => $hobby->id]);
    }

    public function test_admin_delete_user_hobby_not_found()
    {
        list($admin, $token) = $this->authenticate(true);
        $targetUser = User::where('email', 'user@mail.com')->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson("/api/users/{$targetUser->id}/hobbies/9999");

        $response->assertStatus(404);
    }
}
