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
        Schema::create('session_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vr_session_id')->constrained('vr_sessions')->onDelete('cascade');
            $table->integer('total_score')->default(0);
            $table->integer('accuracy_score')->default(0);
            $table->integer('speed_score')->default(0);
            $table->integer('breach_count')->default(0);
            $table->integer('duration_seconds')->default(0);
            $table->integer('completed_steps')->default(0);
            $table->integer('total_steps')->default(0);
            $table->json('metrics_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_analytics');
    }
};
