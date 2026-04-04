<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('feed_url', 500);
            $table->string('website_url', 500);
            $table->string('logo_url', 500)->nullable();
            $table->string('feed_type', 20)->default('rss');
            $table->string('parser_class', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('min_relevance_score')->default(60);
            $table->integer('sync_interval_hours')->default(12);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status', 20)->nullable();
            $table->integer('articles_synced_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
