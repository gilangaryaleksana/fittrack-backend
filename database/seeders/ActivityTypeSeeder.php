<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Same six types the Flutter app ships with.
        $types = [
            ['name' => 'Lari', 'icon' => 'directions_run'],
            ['name' => 'Jalan Kaki', 'icon' => 'directions_walk'],
            ['name' => 'Bersepeda', 'icon' => 'directions_bike'],
            ['name' => 'Renang', 'icon' => 'pool'],
            ['name' => 'Yoga', 'icon' => 'self_improvement'],
            ['name' => 'Gym / Angkat Beban', 'icon' => 'fitness_center'],
        ];

        foreach ($types as $type) {
            ActivityType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
