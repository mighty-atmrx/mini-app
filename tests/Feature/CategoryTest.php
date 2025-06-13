<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_index(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/categories');

        $response->assertOk();

        $response->assertJsonStructure([
            'categories',
            'user_role',
            'pending_reviews'
        ]);
    }

    public function test_categories_create(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/categories', [
                'title' => "тест",
                'subtitle' => 'test',
                'description' => 'testing',
                'position' => 1
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['message']);
    }

    public function test_categories_delete(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $token = JWTAuth::fromUser($user);

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/categories', [
                'title' => "тест",
                'subtitle' => 'test',
                'description' => 'testing',
                'position' => 1
            ]);

        $categoryId = $createResponse->json('id') ?? Category::latest()->first()?->id;

        $this->assertNotNull($categoryId, 'Категория не была создана');

        $deleteResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/categories/{$categoryId}");

        $deleteResponse->assertOk();
        $deleteResponse->assertJsonStructure(['message']);
    }
}
