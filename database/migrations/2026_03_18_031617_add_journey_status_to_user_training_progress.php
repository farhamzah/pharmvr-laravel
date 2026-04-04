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
        Schema::table('user_training_progress', function (Blueprint $table) {
            $table->enum('pre_test_status', ['locked', 'available', 'passed', 'failed'])->default('available')->after('status');
            $table->enum('vr_status', ['locked', 'available', 'completed'])->default('locked')->after('pre_test_status');
            $table->enum('post_test_status', ['locked', 'available', 'passed', 'failed'])->default('locked')->after('vr_status');
            $table->string('last_active_step')->nullable()->after('post_test_status'); // pre_test, vr_sim, post_test
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_training_progress', function (Blueprint $table) {
            $table->dropColumn(['pre_test_status', 'vr_status', 'post_test_status', 'last_active_step']);
        });
    }
};
