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
        Schema::create('custom_chat_files', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('chat_id')->nullable();
            $table->string('vector_id')->nullable();
            $table->string('file_id')->nullable();
            $table->string('url')->nullable();
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
        Schema::dropIfExists('custom_chat_files');
    }
};
