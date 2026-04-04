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
        Schema::create('vr_session_hints', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('vr_session_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('training_module_id')->constrained()->onDelete('cascade');
            
            $blueprint->string('hint_type')->index(); // reminder, warning, guide, explain
            $blueprint->string('trigger_reason'); // time_out, error, proximity, etc.
            $blueprint->string('related_step')->nullable();
            $blueprint->text('displayed_text');
            $blueprint->timestamp('displayed_at')->useCurrent();
            
            $blueprint->timestamps();

            $blueprint->index(['vr_session_id', 'hint_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vr_session_hints');
    }
};
