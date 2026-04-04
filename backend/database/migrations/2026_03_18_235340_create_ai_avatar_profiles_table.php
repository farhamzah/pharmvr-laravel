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
        Schema::create('ai_avatar_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('role_title')->nullable();
            $table->text('persona_text')->nullable();
            $table->text('greeting_text')->nullable();
            $table->foreignId('default_module_id')->nullable()->constrained('training_modules')->onDelete('set null');
            $table->json('allowed_topics_json')->nullable();
            $table->string('avatar_model_path')->nullable();
            $table->string('voice_style')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_avatar_profiles');
    }
};
