<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_show(): void
    {
        $user = User::factory()->create();

        $token = JWTauth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/profile/{$user->id}");

        $response->assertOk();
    }

    public function test_user_cannot_show_other_users(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();
        $otherUserToken = JWTAuth::fromUser($otherUser);

        $response = $this->withHeader('Authorization', 'Bearer ' . $otherUserToken)
            ->getJson("/api/profile/{$user->id}");

        $response->assertStatus(403);
        $response->assertJsonStructure(['error']);
    }

    public function test_unauthorized_user_cannot_show_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson('/api/profile/' . $user->id);

        $response->assertStatus(401);
    }
}
