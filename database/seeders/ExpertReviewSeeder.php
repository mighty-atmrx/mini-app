<?php

namespace Database\Seeders;

use App\Models\Expert;
use App\Models\ExpertReview;
use Illuminate\Database\Seeder;

class ExpertReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExpertReview::factory()->count(7)->create();
        $experts = Expert::with('reviews')->get();
        foreach ($experts as $expert) {
            $rating = ExpertReview::where('expert_id', $expert->id)->avg('rating');
            if ($rating == null) {
                $rating = 0;
            }
            $expert->rating = $rating;
            $expert->save();
        }
    }
}
