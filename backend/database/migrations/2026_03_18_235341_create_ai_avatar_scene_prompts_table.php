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
        Schema::create('ai_avatar_scene_prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('avatar_profile_id')->constrained('ai_avatar_profiles')->onDelete('cascade');
            $table->string('scene_key');
            $table->string('object_key')->nullable();
            $table->string('prompt_title')->nullable();
            $table->text('prompt_text');
            $table->json('suggested_questions_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_avatar_scene_prompts');
    }
};
