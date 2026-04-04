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
        Schema::table('ai_chat_messages', function (Blueprint $table) {
            $table->string('response_mode')->nullable()->after('message_text');
            $table->json('suggested_followups')->nullable()->after('cited_sources_json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_chat_messages', function (Blueprint $table) {
            $table->dropColumn(['response_mode', 'suggested_followups']);
        });
    }
};
