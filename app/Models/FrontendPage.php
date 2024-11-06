<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontendPage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'slug',
        'status',
        'show_main_nav',
        'show_footer_nav',
        'custom',
        'seo_title',
        'seo_url',
        'seo_description',
        'seo_keywords',
    ];
}
