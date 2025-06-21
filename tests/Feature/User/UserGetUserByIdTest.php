<?php

namespace Tests\Feature\User;

use App\Models\Expert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserGetUserByIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_user_by_id(): void
    {
        $tgId = hash('sha256', 234565366);
        $user = User::factory()->create([
            'telegram_user_id' => $tgId,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birthdate' => '1994-04-04',
            'phone' => '+77077777777',
            'role' => 'user'
        ]);

        $authUser = User::factory()->create(['role' => 'expert']);
        Expert::factory()->create(['user_id' => $authUser->id]);
        $token = JWTAuth::fromUser($authUser);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/users/{$user->id}");

        $response->assertOk();
        $response->assertJsonStructure(['user',  'reviews', 'expertCanLeaveReview']);
    }

    public function test_non_expert_cannot_get_user_by_id(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $authUser = User::factory()->create(['role' => 'user']);

        $token = JWTAuth::fromUser($authUser);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/users/{$user->id}");

        $response->assertStatus(404);
        $response->assertJsonStructure(['message']);
    }

    public function test_get_user_by_id_returns_404_if_user_not_found(): void
    {
        $authUser = User::factory()->create(['role' => 'expert']);
        Expert::factory()->create(['user_id' => $authUser->id]);
        $token = JWTAuth::fromUser($authUser);

        $nonExistentUserId = 9999;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/users/{$nonExistentUserId}");

        $response->assertNotFound();
        $response->assertJsonStructure(['message']);
    }

    public function test_get_user_by_id_returns_404_if_expert_not_found(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $authUser = User::factory()->create(['role' => 'expert']);
        $token = JWTAuth::fromUser($authUser);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/users/{$user->id}");

        $response->assertNotFound();
        $response->assertJsonStructure(['message']);
    }
}
