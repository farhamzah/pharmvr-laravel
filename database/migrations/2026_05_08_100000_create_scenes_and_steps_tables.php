<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates scenes and scene_steps tables for WebXR scene registry.
     */
    public function up(): void
    {
        // 1. Scenes - Registry of all WebXR simulation scenes
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_module_id')->constrained()->onDelete('cascade');
            $table->string('slug', 100)->unique();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->json('learning_objectives')->nullable();
            $table->unsignedInteger('order_index')->default(0);
            $table->enum('priority', ['P0', 'P1', 'P2'])->default('P0');
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->unsignedInteger('estimated_minutes')->default(15);
            $table->string('environment_asset', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('required_previous_scene_id')->nullable()->constrained('scenes')->onDelete('set null');
            $table->timestamps();

            $table->index(['training_module_id', 'order_index']);
            $table->index('priority');
        });

        // 2. Scene Steps - Individual steps within each scene
        Schema::create('scene_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_id')->constrained()->onDelete('cascade');
            $table->string('slug', 100);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('order_index')->default(0);
            $table->boolean('is_required')->default(true);
            $table->decimal('scoring_weight', 5, 2)->default(1.00);
            $table->unsignedInteger('max_score')->default(100);
            $table->unsignedInteger('mistake_penalty')->default(10);
            $table->enum('interaction_type', ['click', 'grab', 'sequence', 'inspect', 'toggle', 'navigate', 'observe'])->default('click');
            $table->json('validation_rule')->nullable();
            $table->timestamps();

            $table->unique(['scene_id', 'slug']);
            $table->index(['scene_id', 'order_index']);
        });

        // 3. VR Step Completions - Records per-step completion within a session
        Schema::create('vr_step_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vr_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('scene_step_id')->constrained()->onDelete('cascade');
            $table->decimal('score', 5, 2)->default(0);
            $table->unsignedInteger('time_seconds')->default(0);
            $table->unsignedInteger('mistakes_count')->default(0);
            $table->timestamp('completed_at');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['vr_session_id', 'scene_step_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vr_step_completions');
        Schema::dropIfExists('scene_steps');
        Schema::dropIfExists('scenes');
    }
};
