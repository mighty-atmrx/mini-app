<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminExportStatisticTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_statistic(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $adminToken = JWTauth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->get('/api/statistics');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename=statistic.xlsx');
    }

    public function test_non_admin_cannot_export_statistic(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = JWTauth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/statistics');

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'Доступ запрещен.'
        ]);
    }

    public function test_unauthenticated_user_cannot_export_statistic(): void
    {
        $response = $this->get('/api/statistics');

        $response->assertUnauthorized();
    }
}
