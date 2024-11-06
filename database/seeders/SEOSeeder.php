<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SeoSetting;

class SEOSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ads = [[
            'id' => 1,
            'home_description' => 'Unlock the power of AI content generation with our guide to OpenAI, Claude, Gemini. Discover how to leverage AI tools on DaVinci AI to create anything you prefer.',
            'home_keywords' => 'DaVinci AI, Openai, Gemini, Claude, Stable Diffusion, Dalle',
            'home_author' => 'Berkine',
            'home_title' => 'DaVinci AI - Ultimate AI Content Generator',
            'home_url' => 'https://davinci.berkine.me',
            'login_description' => 'Unlock the power of AI content generation with our guide to OpenAI, Claude, Gemini. Discover how to leverage AI tools on DaVinci AI to create anything you prefer.',
            'login_keywords' => 'DaVinci AI, Openai, Gemini, Claude, Stable Diffusion, Dalle',
            'login_author' => 'Berkine',
            'login_title' => 'DaVinci AI - Ultimate AI Content Generator',
            'login_url'  => 'https://davinci.berkine.me/login',
            'register_description' => 'Unlock the power of AI content generation with our guide to OpenAI, Claude, Gemini. Discover how to leverage AI tools on DaVinci AI to create anything you prefer.',
            'register_keywords' => 'DaVinci AI, Openai, Gemini, Claude, Stable Diffusion, Dalle',
            'register_author' => 'Berkine',
            'register_title' => 'DaVinci AI - Ultimate AI Content Generator',
            'register_url' => 'https://davinci.berkine.me/register',
            'dashboard_description' => 'Unlock the power of AI content generation with our guide to OpenAI, Claude, Gemini. Discover how to leverage AI tools on DaVinci AI to create anything you prefer.',
            'dashboard_keywords' => 'DaVinci AI, Openai, Gemini, Claude, Stable Diffusion, Dalle',
            'dashboard_author' => 'Berkine'
        ]];

        foreach ($ads as $ad) {
            SeoSetting::updateOrCreate(['id' => $ad['id']], $ad);
        }
    }
}
