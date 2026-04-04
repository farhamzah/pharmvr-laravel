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
        Schema::create('vr_ai_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vr_session_id')->nullable()->constrained('vr_sessions')->onDelete('set null');
            $table->foreignId('training_module_id')->nullable()->constrained('training_modules')->onDelete('set null');
            
            $table->string('trigger_event_type')->index();
            $table->enum('hint_type', ['remind', 'warn', 'guide', 'explain', 'feedback'])->default('guide');
            
            $table->json('input_context')->nullable();
            $table->text('output_text');
            $table->boolean('is_voice_suitable')->default(true);
            
            $table->json('metadata')->nullable(); // Latency, model used, etc.
            $table->timestamps();

            $table->index(['vr_session_id', 'hint_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vr_ai_interactions');
    }
};
