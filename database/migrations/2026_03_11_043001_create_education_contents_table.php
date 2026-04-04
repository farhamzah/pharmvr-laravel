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
        Schema::create('education_contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('type', ['module', 'video', 'document']);
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('file_url')->nullable(); // For documents/PDFs
            $table->string('video_id')->nullable(); // For YouTube/Vimeo
            $table->integer('duration_minutes')->nullable();
            $table->integer('pages_count')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education_contents');
    }
};
