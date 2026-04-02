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
        Schema::create('ai_knowledge_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->nullable()->constrained('training_modules')->onDelete('set null');
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('topic')->nullable();
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('source_type');
            $table->string('file_path')->nullable();
            $table->string('language')->default('id');
            $table->string('trust_level')->default('internal');
            $table->string('status')->default('draft');
            $table->boolean('is_active')->default(true);
            $table->enum('parsing_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->enum('indexing_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->unsignedInteger('total_chunks')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_knowledge_sources');
    }
};
