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
        Schema::table('vr_ai_interactions', function (Blueprint $table) {
            $table->string('hint_type')->change()->default('guide');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vr_ai_interactions', function (Blueprint $table) {
            $table->enum('hint_type', ['remind', 'warn', 'guide', 'explain', 'feedback'])->change()->default('guide');
        });
    }
};
