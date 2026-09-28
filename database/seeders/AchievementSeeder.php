<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            ['name' => 'Langkah Pertama', 'description' => 'Menyelesaikan aktivitas pertama', 'icon' => 'emoji_events', 'criteria' => 'activities_count>=1'],
            ['name' => 'Konsisten 7 Hari', 'description' => 'Aktif 7 hari berturut-turut', 'icon' => 'local_fire_department', 'criteria' => 'streak_days>=7'],
            ['name' => '10 Kilometer', 'description' => 'Total jarak mencapai 10 km', 'icon' => 'route', 'criteria' => 'total_distance>=10000'],
            ['name' => '100 Kilometer', 'description' => 'Total jarak mencapai 100 km', 'icon' => 'military_tech', 'criteria' => 'total_distance>=100000'],
            ['name' => 'Pembakar Kalori', 'description' => 'Membakar 5.000 kkal secara total', 'icon' => 'whatshot', 'criteria' => 'total_calories>=5000'],
        ];

        foreach ($achievements as $achievement) {
            Achievement::firstOrCreate(['name' => $achievement['name']], $achievement);
        }
    }
}
