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
        // 1. VR Devices - Represents a paired Meta Quest 3 or Smartphone VR
        Schema::create('vr_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('device_type', ['meta_quest_3', 'smartphone_vr', 'other'])->default('meta_quest_3');
            $table->string('device_name')->nullable();
            $table->string('headset_identifier')->unique(); // Hardware Serial or System ID
            $table->string('platform_name')->nullable(); // e.g., Android, Quest OS
            $table->string('app_version')->nullable();
            $table->string('device_token_hash')->unique()->nullable(); // Hashed bearer token
            $table->enum('status', ['active', 'inactive', 'unlinked'])->default('active');
            $table->timestamp('last_seen_at')->nullable();
            $table->foreignId('current_pairing_id')->nullable(); // Set below after vr_pairings created
            $table->timestamps();

            $table->index('user_id');
            $table->index('device_token_hash');
        });

        // 2. VR Pairings - Managed link between Mobile and VR
        Schema::create('vr_pairings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->nullable()->constrained('vr_devices')->onDelete('cascade');
            $table->string('pairing_code_hash')->index();
            $table->string('pairing_token_hash')->unique();
            $table->enum('status', ['pending', 'confirmed', 'expired', 'cancelled', 'failed'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->foreignId('requested_module_id')->nullable()->constrained('training_modules')->onDelete('set null');
            $table->timestamps();

            $table->index(['status', 'expires_at']);
        });

        // Add foreign key for current_pairing_id to vr_devices
        Schema::table('vr_devices', function (Blueprint $table) {
            $table->foreign('current_pairing_id')->references('id')->on('vr_pairings')->onDelete('set null');
        });

        // 3. VR Sessions - Individual training engagements in XR
        Schema::create('vr_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->constrained('vr_devices')->onDelete('cascade');
            $table->foreignId('training_module_id')->constrained()->onDelete('cascade');
            $table->foreignId('pairing_id')->nullable()->constrained('vr_pairings')->onDelete('set null');
            $table->enum('session_status', ['starting', 'playing', 'completed', 'interrupted', 'failed'])->default('starting');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('interrupted_at')->nullable();
            $table->string('current_step')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->json('summary_json')->nullable();
            $table->timestamps();

            $table->index('session_status');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vr_sessions');
        Schema::table('vr_devices', function (Blueprint $table) {
            $table->dropForeign(['current_pairing_id']);
        });
        Schema::dropIfExists('vr_pairings');
        Schema::dropIfExists('vr_devices');
    }
};
