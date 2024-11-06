<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'gpt_3t',
        'gpt_4t',
        'gpt_4',
        'gpt_4o',
        'gpt_4o_mini',
        'o1_preview',
        'o1_mini',
        'fine_tuned',
        'whisper',
        'dalle_2',
        'dalle_3',
        'dalle_3_hd',
        'claude_3_opus',
        'claude_3_sonnet',
        'claude_3_haiku',
        'gemini_pro',
        'sd',
        'aws_tts',
        'azure_tts',
        'gcp_tts',
        'elevenlabs_tts',
        'openai_tts',
    ];
}

