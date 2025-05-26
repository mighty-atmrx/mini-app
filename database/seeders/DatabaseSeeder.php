<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ExpertSeeder::class,
            ExpertCategorySeeder::class,
            ExpertsScheduleSeeder::class,
            ServiceSeeder::class,
            BookingSeeder::class,
            ExpertReviewSeeder::class,
            UserReviewSeeder::class,
            FavoriteSeeder::class,
        ]);
    }
}
