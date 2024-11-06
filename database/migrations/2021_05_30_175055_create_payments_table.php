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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('frequency')->nullable()->comment('prepaid|monthly|yearly');
            $table->string('order_id')->nullable();
            $table->unsignedBigInteger('plan_id');
            $table->float('price');
            $table->string('currency');
            $table->string('gateway');
            $table->string('status')->comment('completed|cancelled|declined|failed|pending');
            $table->string('plan_name');
            $table->integer('words')->nullable()->default(0);
            $table->integer('images')->nullable()->default(0);
            $table->integer('characters')->nullable()->default(0);
            $table->integer('minutes')->nullable()->default(0);
            $table->dateTime('valid_until')->nullable();
            $table->integer('dalle_images')->nullable()->default(0);
            $table->integer('sd_images')->nullable()->default(0);
            $table->string('invoice')->nullable();
            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_vat_number')->nullable();
            $table->text('billing_address')->nullable();
            $table->integer('gpt_3_turbo_credits')->default(0);
            $table->integer('gpt_4_turbo_credits')->default(0);
            $table->integer('gpt_4_credits')->default(0);
            $table->integer('gpt_4o_credits')->default(0);
            $table->integer('gpt_4o_mini_credits')->default(0);
            $table->integer('claude_3_opus_credits')->default(0);
            $table->integer('claude_3_sonnet_credits')->default(0);
            $table->integer('claude_3_haiku_credits')->default(0);
            $table->integer('fine_tune_credits')->default(0);
            $table->integer('gemini_pro_credits')->default(0);
            $table->integer('o1_mini_credits')->default(0);
            $table->integer('o1_preview_credits')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
