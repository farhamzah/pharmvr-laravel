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
            $table->string('code')->nullable()->after('id'); // e.g., 'MP-001'
            $table->json('learning_path')->nullable()->after('tags'); // Stores pre_test, vr_sim, post_test status/links
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education_contents', function (Blueprint $table) {
            $table->dropColumn(['code', 'learning_path']);
        });
    }
};
