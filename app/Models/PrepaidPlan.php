<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrepaidPlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status',
        'plan_name',
        'price',
        'currency',
        'words',
        'images',
        'featured',
        'pricing_plan',
        'characters',
        'minutes',
        'model',
        'dalle_images',
        'sd_images',
        'gpt_3_turbo_credits_prepaid',
        'gpt_4_turbo_credits_prepaid',
        'gpt_4_credits_prepaid',
        'gpt_4o_credits_prepaid',
        'claude_3_opus_credits_prepaid',
        'claude_3_sonnet_credits_prepaid',
        'claude_3_haiku_credits_prepaid',
        'fine_tune_credits_prepaid',
        'gemini_pro_credits_prepaid',
        'gpt_4o_mini_credits_prepaid',
        'o1_preview_credits_prepaid',
        'o1_mini_credits_prepaid',
    ];
}
