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
        Schema::create('vendor_prices', function (Blueprint $table) {
            $table->id();
            $table->double('gpt_3t')->default(0);
            $table->double('gpt_4t')->default(0);
            $table->double('gpt_4')->default(0);
            $table->double('gpt_4o')->default(0);
            $table->double('gpt_4o_mini')->default(0);
            $table->double('o1_mini')->default(0);
            $table->double('o1_preview')->default(0);
            $table->double('fine_tuned')->default(0);
            $table->double('whisper')->default(0);
            $table->double('dalle_2')->default(0);
            $table->double('dalle_3')->default(0);
            $table->double('dalle_3_hd')->default(0);
            $table->double('claude_3_opus')->default(0);
            $table->double('claude_3_sonnet')->default(0);
            $table->double('claude_3_haiku')->default(0);
            $table->double('gemini_pro')->default(0);
            $table->double('sd')->default(0);
            $table->double('aws_tts')->default(0);
            $table->double('azure_tts')->default(0);
            $table->double('gcp_tts')->default(0);
            $table->double('elevenlabs_tts')->default(0);
            $table->double('openai_tts')->default(0);
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
        Schema::dropIfExists('vendor_prices');
    }
};
