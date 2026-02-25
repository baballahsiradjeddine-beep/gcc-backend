<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'ناشئ',
                'min_points' => 50,
                'max_points' => 200,
                'color' => '#2DD4BF',
                'rank_order' => 1,
            ],
            [
                'name' => 'شاطر',
                'min_points' => 200,
                'max_points' => 500,
                'color' => '#F59E0B',
                'rank_order' => 2,
            ],
            [
                'name' => 'بطل',
                'min_points' => 501,
                'max_points' => 1000,
                'color' => '#EF4444',
                'rank_order' => 3,
            ],
            [
                'name' => 'خباش',
                'min_points' => 1001,
                'max_points' => 2000,
                'color' => '#3B82F6',
                'rank_order' => 4,
            ],
            [
                'name' => 'بروف',
                'min_points' => 2001,
                'max_points' => null,
                'color' => '#8B5CF6',
                'rank_order' => 5,
            ],
        ];

        foreach ($badges as $badgeData) {
            Badge::updateOrCreate(['name' => $badgeData['name']], $badgeData);
        }
    }
}
