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
        Schema::create('vr_session_stage_results', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('vr_session_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('training_module_id')->constrained()->onDelete('cascade');
            
            $blueprint->string('stage_name')->index();
            $blueprint->decimal('stage_score', 8, 2)->nullable();
            $blueprint->boolean('passed')->default(false);
            $blueprint->timestamp('submitted_at')->useCurrent();
            $blueprint->json('metadata')->nullable();
            
            $blueprint->timestamps();

            $blueprint->index(['vr_session_id', 'stage_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vr_session_stage_results');
    }
};
