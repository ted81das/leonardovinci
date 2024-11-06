<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SdCost extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sd_photo_studio_reimagine',
        'sd_photo_studio_inpaint',
        'sd_photo_studio_search_replace',
        'sd_photo_studio_outpaint',
        'sd_photo_studio_erase_object',
        'sd_photo_studio_remove_background',
        'sd_photo_studio_structure',
        'sd_photo_studio_sketch',
        'sd_photo_studio_creative_upscaler',
        'sd_photo_studio_conservative_upscaler',
        'sd_photo_studio_text',
        'sd_photo_studio_style',
        'sd_photo_studio_3d',
    ];
}

