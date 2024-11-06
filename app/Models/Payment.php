<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * Payment belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'order_id',
        'plan_id',
        'price',
        'currency',
        'gateway',
        'status',
        'plan_name',
        'words',
        'valid_until',
        'images',
        'characters',
        'minutes',
        'dalle_images',
        'sd_images',
        'invoice',
        'billing_first_name',
        'billing_last_name',
        'billing_email',
        'billing_phone',
        'billing_city',
        'billing_postal_code',
        'billing_country',
        'billing_vat_number',
        'billing_address',
        'gpt_3_turbo_credits',
        'gpt_4_turbo_credits',
        'gpt_4_credits',
        'gpt_4o_credits',
        'gpt_4o_mini_credits',
        'o1_preview_credits',
        'o1_mini_credits',
        'claude_3_opus_credits',
        'claude_3_sonnet_credits',
        'claude_3_haiku_credits',
        'fine_tune_credits',
        'gemini_pro_credits'
    ];
}
