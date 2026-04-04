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
        Schema::create('ai_knowledge_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('ai_knowledge_sources')->onDelete('cascade');
            $table->unsignedInteger('chunk_index');
            $table->string('section_title')->nullable();
            $table->unsignedInteger('page_number')->nullable();
            $table->longText('chunk_text');
            $table->unsignedInteger('token_count')->nullable();
            $table->enum('embedding_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('chunk_hash')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_knowledge_chunks');
    }
};
