<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VendorPrice;

class PricesSeeder extends Seeder
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
                'gpt_3t' => 0.0015,
                'gpt_4t' => 0.03,
                'gpt_4' => 0.06,
                'gpt_4o' => 0.015,
                'gpt_4o_mini' => 0.0006,
                'fine_tuned' => 0.003,
                'whisper' => 0.006,
                'dalle_2' => 0.02,
                'dalle_3' => 0.08,
                'dalle_3_hd' => 0.12,
                'claude_3_opus' => 0.075,
                'claude_3_sonnet' => 0.015,
                'claude_3_haiku' => 0.00125,
                'gemini_pro' => 0.00125,
                'sd' => 10,
                'aws_tts' => 16,
                'azure_tts' => 15,
                'gcp_tts' => 16,
                'elevenlabs_tts' => 200,
                'openai_tts' => 30,
            ]

        ];

        foreach ($ads as $ad) {
            VendorPrice::updateOrCreate(['id' => $ad['id']], $ad);
        }
    }
}
