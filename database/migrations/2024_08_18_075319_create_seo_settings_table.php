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
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('home_title')->nullable();
            $table->string('home_author')->nullable();
            $table->string('home_url')->nullable();
            $table->longText('home_description')->nullable();
            $table->longText('home_keywords')->nullable();
            $table->string('login_title')->nullable();
            $table->string('login_author')->nullable();
            $table->string('login_url')->nullable();
            $table->longText('login_description')->nullable();
            $table->longText('login_keywords')->nullable();
            $table->string('register_title')->nullable();
            $table->string('register_author')->nullable();
            $table->string('register_url')->nullable();
            $table->longText('register_description')->nullable();
            $table->longText('register_keywords')->nullable();
            $table->string('dashboard_author')->nullable();
            $table->longText('dashboard_description')->nullable();
            $table->longText('dashboard_keywords')->nullable();
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
        Schema::dropIfExists('seo_settings');
    }
};
