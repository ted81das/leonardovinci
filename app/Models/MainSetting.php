<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'youtube_feature',
        'youtube_api',
        'rss_feature',
        'youtube_feature_free_tier',
        'rss_feature_free_tier',
        'gpt_4o_mini_credits',
        'o1_preview_credits',
        'o1_mini_credits',
        'languages',
        'default_language', 
        'weekly_reports',
        'monthly_reports',
        'frontend_theme',
        'dashboard_theme',
        'logo_frontend',
        'logo_frontend_collapsed',
        'logo_frontend_footer',
        'logo_dashboard',
        'logo_dashboard_dark',
        'logo_dashboard_collapsed',
        'logo_dashboard_collapsed_dark',
    ];
}
