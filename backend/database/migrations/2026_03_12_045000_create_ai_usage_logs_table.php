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
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Interaction Classification
            $table->string('interaction_type'); // e.g., 'app_chat', 'vr_hint', 'vr_feedback'
            $table->string('source_type');      // e.g., 'PharmaiMessage', 'VrAiInteraction'
            $table->unsignedBigInteger('source_id');
            
            // AI Provider Details
            $table->string('provider_name');    // e.g., 'openai', 'gemini', 'mock'
            $table->string('model_name')->nullable();
            
            // Performance & Cost Metrics
            $table->integer('latency_ms')->nullable();
            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->integer('total_tokens')->nullable();
            
            // Domain & Safety
            $table->string('domain_mode')->nullable(); // e.g., 'pharmacist', 'cleanroom_supervisor'
            $table->boolean('is_safe_response')->default(true);
            
            // Optional context IDs
            $table->foreignId('conversation_id')->nullable()->constrained('pharmai_conversations')->onDelete('cascade');
            $table->foreignId('vr_session_id')->nullable()->constrained('vr_sessions')->onDelete('cascade');

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['interaction_type', 'user_id']);
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
