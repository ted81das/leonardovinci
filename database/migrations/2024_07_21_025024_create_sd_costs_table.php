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
        Schema::create('sd_costs', function (Blueprint $table) {
            $table->id();
            $table->integer('sd_photo_studio_reimagine')->default(3);
            $table->integer('sd_photo_studio_inpaint')->default(3);
            $table->integer('sd_photo_studio_search_replace')->default(4);
            $table->integer('sd_photo_studio_outpaint')->default(4);
            $table->integer('sd_photo_studio_erase_object')->default(3);
            $table->integer('sd_photo_studio_remove_background')->default(2);
            $table->integer('sd_photo_studio_structure')->default(3);
            $table->integer('sd_photo_studio_sketch')->default(3);
            $table->integer('sd_photo_studio_creative_upscaler')->default(25);
            $table->integer('sd_photo_studio_conservative_upscaler')->default(25);
            $table->integer('sd_photo_studio_text')->default(1);
            $table->integer('sd_photo_studio_style')->default(4);
            $table->integer('sd_photo_studio_3d')->default(1);
            $table->integer('sd_ultra')->default(8);
            $table->integer('sd_core')->default(3);
            $table->integer('sd_3_medium')->default(3);
            $table->integer('sd_3_large')->default(6);
            $table->integer('sd_3_large_turbo')->default(4);
            $table->integer('sd_video')->default(20);
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
        Schema::dropIfExists('sd_costs');
    }
};
