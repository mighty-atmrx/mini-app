<?php

namespace Database\Seeders;

use App\Models\ExpertCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpertCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExpertCategory::factory()->count(2)->create();
    }
}
