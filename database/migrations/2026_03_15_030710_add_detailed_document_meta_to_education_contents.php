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
        Schema::table('education_contents', function (Blueprint $table) {
            $table->string('short_summary')->nullable()->after('description');
            $table->text('ai_context')->nullable()->after('related_materials');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education_contents', function (Blueprint $table) {
            $table->dropColumn(['short_summary', 'ai_context']);
        });
    }
};
