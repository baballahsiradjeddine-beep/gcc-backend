<?php

namespace Database\Seeders;

use App\Models\AppAsset;
use Illuminate\Database\Seeder;

class AppAssetSeeder extends Seeder
{
    public function run(): void
    {
        foreach (AppAsset::DEFAULT_ASSETS as $key => $info) {
            AppAsset::firstOrCreate(
                ['key' => $key],
                [
                    'label'       => $info['label'],
                    'description' => $info['description'],
                    'image_url'   => null,
                    'is_active'   => true,
                ]
            );
        }
    }
}
