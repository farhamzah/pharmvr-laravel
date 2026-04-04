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
        Schema::create('vr_session_events', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('vr_session_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('training_module_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('device_id')->nullable()->constrained('vr_devices')->onDelete('set null');
            
            $blueprint->string('event_type')->index();
            $blueprint->timestamp('event_timestamp');
            $blueprint->json('event_payload')->nullable();
            
            $blueprint->timestamps();

            $blueprint->index(['vr_session_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vr_session_events');
    }
};
