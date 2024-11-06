<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prepaid_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name');
            $table->decimal('price', 15, 2)->unsigned();
            $table->string('currency')->default('USD');
            $table->string('status')->default('active')->comment('active|closed');
            $table->integer('words')->default(0);
            $table->integer('images')->default(0);
            $table->integer('characters')->default(0);
            $table->integer('minutes')->default(0);
            $table->boolean('featured')->nullable()->default(false);
            $table->string('pricing_plan')->default('prepaid');
            $table->string('model')->nullable();
            $table->integer('dalle_images')->nullable()->default(0);
            $table->integer('sd_images')->nullable()->default(0);
            $table->integer('gpt_3_turbo_credits_prepaid')->default(0);
            $table->integer('gpt_4_turbo_credits_prepaid')->default(0);
            $table->integer('gpt_4_credits_prepaid')->default(0);
            $table->integer('gpt_4o_credits_prepaid')->default(0);
            $table->integer('claude_3_opus_credits_prepaid')->default(0);
            $table->integer('claude_3_sonnet_credits_prepaid')->default(0);
            $table->integer('claude_3_haiku_credits_prepaid')->default(0);
            $table->integer('fine_tune_credits_prepaid')->default(0);
            $table->integer('gemini_pro_credits_prepaid')->default(0);
            $table->integer('gpt_4o_mini_credits_prepaid')->default(0);
            $table->integer('o1_mini_credits_prepaid')->default(0);
            $table->integer('o1_preview_credits_prepaid')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prepaid_plans');
    }
};
