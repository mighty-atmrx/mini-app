<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserReviews;
use Illuminate\Database\Seeder;

class UserReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserReviews::factory()->count(3)->create();
        $users = User::with('reviews')->get();
        foreach ($users as $user) {
            $rating = UserReviews::where('user_id', $user->id)->avg('rating');
            if ($rating == null) {
                $rating = 0;
            }
            $user->rating = $rating;
            $user->save();
        }
    }
}
