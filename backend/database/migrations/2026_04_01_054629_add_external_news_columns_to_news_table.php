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
        Schema::table('news', function (Blueprint $table) {
            $table->enum('content_type', ['internal', 'external'])->default('internal')->after('id');
            $table->foreignId('news_source_id')->nullable()->after('content_type')->constrained('news_sources')->nullOnDelete();
            $table->string('original_url', 700)->nullable();
            $table->string('author', 200)->nullable();
            $table->string('source_name', 100)->nullable();
            $table->text('ai_summary')->nullable();
            $table->json('ai_tags')->nullable();
            $table->string('topic_category', 50)->nullable();
            $table->integer('relevance_score')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->string('content_hash', 64)->nullable()->unique();
            
            $table->index('content_type');
            $table->index('relevance_score');
            $table->index('topic_category');
            $table->index(['news_source_id', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropForeign(['news_source_id']);
            $table->dropIndex(['content_type']);
            $table->dropIndex(['relevance_score']);
            $table->dropIndex(['topic_category']);
            $table->dropIndex(['news_source_id', 'published_at']);
            
            $table->dropColumn([
                'content_type',
                'news_source_id',
                'original_url',
                'author',
                'source_name',
                'ai_summary',
                'ai_tags',
                'topic_category',
                'relevance_score',
                'is_pinned',
                'is_hidden',
                'content_hash',
            ]);
        });
    }
};
