<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_store(): void
    {
        $plainId = '723476982';
        $data = [
            'telegram_user_id' => $plainId,
            'first_name' => 'Антон',
            'last_name' => 'Копченый',
            'birthdate' => '01.01.2001',
            'phone' => '+77088883344',
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertOk();
        $response->assertJsonStructure(['message']);

        $this->assertDatabaseHas('users', [
            'telegram_user_id' => hash('sha256', $plainId),
            'first_name' => 'Антон',
            'role' => 'user',
        ]);
    }

    public function test_user_store_fails_with_missing_fields(): void
    {
        $response = $this->postJson('/api/users', []);

        $response->assertStatus(500);
    }

    public function test_user_store_fails_with_duplicate_telegram_id(): void
    {
        User::factory()->create([
            'telegram_user_id' => hash('sha256', '723476982'),
            'first_name' => 'Андрей',
            'last_name' => 'Андреев',
            'birthdate' => '2025-02-02',
            'phone' => '+77087773355',
        ]);

        $data = [
            'telegram_user_id' => '723476982',
            'first_name' => 'Антон',
            'last_name' => 'Копченый',
            'birthdate' => '01.01.2001',
            'phone' => '+77088883344',
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(500);
    }
}
