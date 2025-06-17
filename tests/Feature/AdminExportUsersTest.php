<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminExportUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $adminToken = JWTauth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->get('/api/users-to-excel');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename=users.xlsx');
    }

    public function test_non_admin_cannot_export_users(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = JWTauth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/users-to-excel');

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'Доступ запрещен.'
        ]);
    }

    public function test_unauthenticated_user_cannot_export_users(): void
    {
        $response = $this->get('/api/users-to-excel');

        $response->assertUnauthorized();
    }
}
