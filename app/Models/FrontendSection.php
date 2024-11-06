<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontendSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'main_banner_pretitle',
        'main_banner_title',
        'main_banner_carousel',
        'main_banner_subtitle',
        'how_it_works_status',
        'how_it_works_title',
        'how_it_works_subtitle',
        'tools_status',
        'tools_title',
        'tools_subtitle',
        'templates_status',
        'templates_title',
        'templates_subtitle',
        'features_status',
        'features_title',
        'features_subtitle',
        'features_description',
        'pricing_status',
        'pricing_title',
        'pricing_subtitle',
        'pricing_description',
        'reviews_status',
        'reviews_title',
        'reviews_subtitle',
        'faq_status',
        'faq_title',
        'faq_subtitle',
        'faq_description',
        'blogs_status',
        'blogs_title',
        'blogs_subtitle',
        'images_status',
        'images_title',
        'images_subtitle',
        'info_status',
        'info_title',
        'clients_status',
        'clients_title',
        'contact_status',
        'contact_location',
        'contact_email',
        'contact_phone',
        'how_it_works_description',
        'tools_description',
        'templates_description',
        'reviews_description',
        'blog_description',
        'info_description',
        'images_description',
        'clients_title_dark',
    ];
}

