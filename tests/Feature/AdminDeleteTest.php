<?php

namespace Tests\Feature;

use App\Models\Expert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_expert(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $token = JWTAuth::fromUser($user);

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/experts', [
                'first_name' => 'Name',
                'last_name' => 'Last Name',
                'profession' => 'Profession',
                'biography' => 'Biography',
                'photo' => UploadedFile::fake()->image('avatar.jpg'),
                'experience' => 'Experience',
                'education' => 'Education',
            ]);

        $createResponse->assertCreated();

        $expertId = $createResponse->json('id') ?? Expert::latest()->first()?->id;

        $this->assertNotNull($expertId, 'Эксперт не был найден');

        $admin = User::factory()->create(['role' => 'admin']);
        $adminToken = JWTauth::fromUser($admin);

        $deleteResponse = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->deleteJson('/api/experts/' . $expertId);

        $deleteResponse->assertOk();
        $deleteResponse->assertJsonStructure(['message']);
        $this->assertDatabaseMissing('experts', ['id' => $expertId]);
    }

    public function test_delete_user(): void
    {
        $user = User::factory()->create([
            'telegram_user_id' => 74873920,
            'first_name' => 'Name',
            'last_name' => 'Last Name',
            'birthdate' => '1995-05-05',
            'phone' => '+7701324365',
            'role' => 'user',
            'rating' => 4.5
        ]);
        $userId = $user->id;

        $this->assertNotNull($userId, 'Пользователь не был найден');

        $admin = User::factory()->create(['role' => 'admin']);
        $adminToken = JWTauth::fromUser($admin);

        $deleteResponse = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->deleteJson('/api/users/' . $userId);

        $deleteResponse->assertOk();
        $deleteResponse->assertJsonStructure(['message']);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }
}
