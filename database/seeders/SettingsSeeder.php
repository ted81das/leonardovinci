<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MainSetting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ads = [
            ['id' => 1, 'languages' => 'en,es', 'default_language' => 'en'],

        ];

        foreach ($ads as $ad) {
            MainSetting::updateOrCreate(['id' => $ad['id']], $ad);
        }
    }
}
