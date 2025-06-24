<?php

namespace Tests\Feature\User;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Expert;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserGetCompletedBookingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_valid_completed_bookings(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $telegram_id = 473547834;
        TelegraphChat::factory()->create(['chat_id' => $telegram_id]);
        $expertUser = User::factory()->create([
            'role' => 'expert',
            'telegram_user_id' => hash('sha256', $telegram_id)
        ]);
        $expert = Expert::factory()->create(['user_id' => $expertUser->id]);

        $category = Category::factory()->create();

        $service = Service::factory()->create([
            'expert_id' => $expert->id,
            'category_id' => $category->id
        ]);

        Booking::factory()->create([
            'status' => 'completed',
            'user_id' => $user->id,
            'expert_id' => $expert->id,
            'service_id' => $service->id,
            'date' => Carbon::yesterday()->format('Y-m-d'),
            'time' => Carbon::yesterday()->format('H:i'),
        ]);

        Booking::factory()->create([
            'status' => 'completed',
            'user_id' => $user->id,
            'expert_id' => $expert->id,
            'service_id' => $service->id,
            'date' => Carbon::yesterday()->format('Y-m-d'),
            'time' => Carbon::yesterday()->format('H:i'),
        ]);

        Booking::factory()->create([
            'status' => 'paid',
            'user_id' => $user->id,
            'expert_id' => $expert->id,
            'service_id' => $service->id,
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'time' => Carbon::tomorrow()->format('H:i'),
        ]);

        Booking::factory()->create([
            'status' => 'payment',
            'user_id' => $user->id,
            'expert_id' => $expert->id,
            'service_id' => $service->id,
            'date' => null,
            'time' => null,
        ]);


        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/get-user-completed-bookings');

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['service_title', 'date', 'time',
                'expert_first_name', 'expert_last_name',
                'expert_photo', 'expert_id', 'expert_rating',
                'date_of_purchase', 'expert_username', 'expert_phone']
        ]);

        $responseData = $response->json();
        $this->assertCount(2, $responseData);
    }

    public function test_it_filters_out_invalid_bookings(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $telegram_id = 473547834;
        TelegraphChat::factory()->create(['chat_id' => $telegram_id]);
        $expertUser = User::factory()->create([
            'role' => 'expert',
            'telegram_user_id' => hash('sha256', $telegram_id)
        ]);
        $expert = Expert::factory()->create(['user_id' => $expertUser->id]);

        $category = Category::factory()->create();

        $service = Service::factory()->create([
            'expert_id' => $expert->id,
            'category_id' => $category->id
        ]);

        Booking::factory()->create([
            'status' => 'paid',
            'user_id' => $user->id,
            'expert_id' => $expert->id,
            'service_id' => $service->id,
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'time' => Carbon::tomorrow()->format('H:i'),
        ]);

        Booking::factory()->create([
            'status' => 'paid',
            'user_id' => $user->id,
            'expert_id' => $expert->id,
            'service_id' => $service->id,
            'date' => null,
            'time' => null,
        ]);

        Booking::factory()->create([
            'status' => 'payment',
            'user_id' => $user->id,
            'expert_id' => $expert->id,
            'service_id' => $service->id,
            'date' => null,
            'time' => null,
        ]);


        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/get-user-completed-bookings');

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['service_title', 'date', 'time',
                'expert_first_name', 'expert_last_name',
                'expert_photo', 'expert_id', 'expert_rating',
                'date_of_purchase', 'expert_username', 'expert_phone']
        ]);

        $responseData = $response->json();
        $this->assertCount(0, $responseData);
    }
}
