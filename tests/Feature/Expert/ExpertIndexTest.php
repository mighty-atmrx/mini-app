<?php

namespace Tests\Feature\Expert;

use App\Models\Expert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExpertIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_experts_for_authenticated_user(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $user = User::factory()->create();
            Expert::factory()->create(['user_id' => $user->id]);
        }

        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/experts');

        $response->assertOk();
        $response->assertJsonCount(5, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'user_id', 'first_name', 'last_name',
                    'profession', 'biography', 'photo', 'experience',
                    'education', 'rating', 'created_at', 'updated_at',
                    'categories'
                ]
            ]
        ]);
    }

    public function test_unauthorized_user_cannot_get_all_experts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $user = User::factory()->create();
            Expert::factory()->create(['user_id' => $user->id]);
        }

        $response = $this->getJson('/api/experts');

        $response->assertUnauthorized();
    }
}
