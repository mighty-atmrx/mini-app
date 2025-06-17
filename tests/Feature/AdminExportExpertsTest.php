<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminExportExpertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_experts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $adminToken = JWTauth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->get('/api/experts-to-excel');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename=experts.xlsx');
    }

    public function test_non_admin_cannot_export_experts(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = JWTauth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/experts-to-excel');

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'Доступ запрещен.'
        ]);
    }

    public function test_unauthenticated_user_cannot_export_experts(): void
    {
        $response = $this->get('/api/experts-to-excel');

        $response->assertUnauthorized();
    }
}
