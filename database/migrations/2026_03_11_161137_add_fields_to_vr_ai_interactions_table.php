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
            $table->text('display_text')->nullable()->after('output_text');
            $table->text('speech_text')->nullable()->after('display_text');
            $table->string('severity')->default('info')->after('speech_text');
            $table->text('recommended_next_action')->nullable()->after('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vr_ai_interactions', function (Blueprint $table) {
            $table->dropColumn(['display_text', 'speech_text', 'severity', 'recommended_next_action']);
        });
    }
};
