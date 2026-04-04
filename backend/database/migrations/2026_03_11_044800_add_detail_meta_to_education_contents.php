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
            $table->string('related_topic')->nullable()->after('category');
            $table->string('next_step_label')->nullable()->after('learning_path');
            $table->string('next_step_action')->nullable()->after('next_step_label'); // e.g., 'open_module', 'open_ai', 'open_simulation'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education_contents', function (Blueprint $table) {
            $table->dropColumn(['related_topic', 'next_step_label', 'next_step_action']);
        });
    }
};
