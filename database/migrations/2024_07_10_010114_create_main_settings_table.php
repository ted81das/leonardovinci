<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('main_settings', function (Blueprint $table) {
            $table->id();
            $table->text('languages');
            $table->string('default_language');
            $table->boolean('youtube_feature')->nullable()->default(0);
            $table->string('youtube_api')->nullable();
            $table->boolean('youtube_feature_free_tier')->nullable()->default(0);
            $table->boolean('rss_feature')->nullable()->default(0);
            $table->boolean('rss_feature_free_tier')->nullable()->default(0);
            $table->integer('gpt_4o_mini_credits')->nullable()->default(0);
            $table->integer('o1_mini_credits')->nullable()->default(0);
            $table->integer('o1_preview_credits')->nullable()->default(0);
            $table->boolean('weekly_reports')->nullable()->default(0);
            $table->boolean('monthly_reports')->nullable()->default(0);
            $table->string('frontend_theme')->default('default');
            $table->string('dashboard_theme')->default('default');
            $table->string('logo_frontend')->default('uploads/logo/frontend-logo.png');
            $table->string('logo_frontend_collapsed')->default('uploads/logo/frontend-collapsed-logo.png');
            $table->string('logo_frontend_footer')->default('uploads/logo/frontend-footer-logo.png');
            $table->string('logo_dashboard')->default('uploads/logo/dashboard-logo.png');
            $table->string('logo_dashboard_dark')->default('uploads/logo/dashboard-dark-logo.png');
            $table->string('logo_dashboard_collapsed')->default('uploads/logo/dashboard-collapsed-logo.png');
            $table->string('logo_dashboard_collapsed_dark')->default('uploads/logo/dashboard-collapsed-dark-logo.png');
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
        Schema::dropIfExists('main_settings');
    }
};
