<?php

namespace Database\Seeders;

use App\Models\People;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create();

        // Generate 50 people
        People::factory(50)->create()->each(function ($people) {
            Picture::factory(rand(1, 4))->create([
                'people_id' => $people->id,
            ]);
        });
    }
}
