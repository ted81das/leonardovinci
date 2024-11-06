<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SdCost;

class SDSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ads = [
            ['id' => 1, 
                'sd_photo_studio_inpaint' => 3,
                'sd_photo_studio_search_replace' => 4,
                'sd_photo_studio_outpaint' => 4,
                'sd_photo_studio_erase_object' => 3,
                'sd_photo_studio_remove_background' => 2,
                'sd_photo_studio_structure' => 3,
                'sd_photo_studio_sketch' => 3,
                'sd_photo_studio_creative_upscaler' => 25,
                'sd_photo_studio_conservative_upscaler' => 25,
                'sd_photo_studio_reimagine' => 6,
                'sd_ultra' => 8,
                'sd_core' => 3,
                'sd_3_medium' => 3,
                'sd_3_large' => 6,
                'sd_3_large_turbo' => 4,
                'sd_video' => 20,
                'sd_photo_studio_style' => 4,
                'sd_photo_studio_3d' => 2,
            ]

        ];

        foreach ($ads as $ad) {
            SdCost::updateOrCreate(['id' => $ad['id']], $ad);
        }
    }
}
