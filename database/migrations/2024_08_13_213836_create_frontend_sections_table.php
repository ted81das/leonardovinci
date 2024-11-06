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
        Schema::create('frontend_sections', function (Blueprint $table) {
            $table->id();
            $table->string('main_banner_pretitle')->nullable();
            $table->string('main_banner_title')->nullable();
            $table->text('main_banner_carousel')->nullable();
            $table->string('main_banner_subtitle')->nullable();
            $table->boolean('how_it_works_status')->default(true);
            $table->string('how_it_works_title')->nullable();
            $table->string('how_it_works_subtitle')->nullable();
            $table->text('how_it_works_description')->nullable();
            $table->boolean('tools_status')->default(true);
            $table->string('tools_title')->nullable();
            $table->string('tools_subtitle')->nullable();
            $table->text('tools_description')->nullable();
            $table->boolean('templates_status')->default(true);
            $table->string('templates_title')->nullable();
            $table->string('templates_subtitle')->nullable();
            $table->text('templates_description')->nullable();
            $table->boolean('features_status')->default(true);
            $table->string('features_title')->nullable();
            $table->string('features_subtitle')->nullable();
            $table->text('features_description')->nullable();
            $table->boolean('pricing_status')->default(true);
            $table->string('pricing_title')->nullable();
            $table->string('pricing_subtitle')->nullable();
            $table->text('pricing_description')->nullable();
            $table->boolean('reviews_status')->default(true);
            $table->string('reviews_title')->nullable();
            $table->string('reviews_subtitle')->nullable();
            $table->text('reviews_description')->nullable();
            $table->boolean('faq_status')->default(true);
            $table->string('faq_title')->nullable();
            $table->string('faq_subtitle')->nullable();
            $table->text('faq_description')->nullable();
            $table->boolean('blogs_status')->default(true);
            $table->string('blogs_title')->nullable();
            $table->string('blogs_subtitle')->nullable();
            $table->text('blogs_description')->nullable();
            $table->boolean('info_status')->default(true);
            $table->string('info_title')->nullable();
            $table->text('info_description')->nullable();
            $table->boolean('images_status')->default(true);
            $table->string('images_title')->nullable();
            $table->string('images_subtitle')->nullable();
            $table->text('images_description')->nullable();
            $table->boolean('clients_status')->default(true);
            $table->string('clients_title')->nullable();
            $table->string('clients_title_dark')->nullable();
            $table->boolean('contact_status')->default(true);
            $table->string('contact_location')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
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
        Schema::dropIfExists('frontend_sections');
    }
};
