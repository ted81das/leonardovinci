<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FrontendSection;

class SectionsSeeder extends Seeder
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
            'main_banner_pretitle' => 'Meet Davinci',
            'main_banner_title' => 'Ultimate AI Creator',
            'main_banner_carousel' => 'Article Generator,Content Improver,Blog Contents,Ad Creations,Text to Speech,And Many More!',
            'main_banner_subtitle' => 'One platform to generate all AI contents & AI Voiceovers',
            'how_it_works_status' => true,
            'how_it_works_title' => '<span>How</span> does it work?',
            'how_it_works_subtitle' => 'Start Writing in 3 Easy Steps',
            'tools_status' =>  true,
            'tools_title' => 'The <span>Ultimate Power</span> of AI',
            'tools_subtitle' => 'Discover available AI tools',
            'templates_status' =>  true,
            'templates_title' => '<span> Unlimited Templates </span>to get started',
            'templates_subtitle' => 'Custom Templates',
            'features_status' =>  true,
            'features_title' => '<span> Only platform </span>that you will ever need',
            'features_subtitle' => 'Features List',
            'features_description' => 'We have you covered from all AI Text & Audio generation aspects to allow you to focus only on your content creation',
            'pricing_status' =>  true,
            'pricing_title' => '<span>Simple</span> Pricing, <span>Unbeatable</span> Value',
            'pricing_subtitle' => 'Our Pricing',
            'pricing_description' => 'Subscribe to your preferred Subscription Plan or Top Up your credits and get started',
            'reviews_status' =>  true,
            'reviews_title' => 'Be one of our <span>Happy Customers</span>',
            'reviews_subtitle' => 'Testimonials & Reviews',
            'faq_status' =>  true,
            'faq_title' => '<span>Got Questions?</span> We have you covered',
            'faq_subtitle' => 'Frequently Asked Questions',
            'faq_description' => 'We are always here to provide full support and clear any doubts that you might have',
            'blogs_status' =>  true,
            'blogs_title' => 'Our Latest <span>Blogs</span>',
            'blogs_subtitle' => 'Stay up to Date',
            'info_status' =>  true,
            'info_title' => 'What else is <span>there?</span>',
            'images_status' =>  true,
            'images_title' => 'Visualize your <span class="text-primary">Dream</span>',
            'images_subtitle' => 'Create AI Art and Images with Text',
            'clients_status' =>  true,
            'clients_title' => 'Join the 10.000+ Companies trusting DaVinci AI',
            'contact_status' => true,
            'contact_location' => '409 Oliver Street, 59018, Bozeman, MT, USA',
            'contact_email' => 'info@davinci.ai',
            'contact_phone' => '+1 (404) 594 4040',
        ]];

        foreach ($ads as $ad) {
            FrontendSection::updateOrCreate(['id' => $ad['id']], $ad);
        }
    }
}
