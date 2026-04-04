<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Standardize role and add status
            $table->string('status')->default('active')->after('role');
            
            // Temporary change of role default to student
            $table->string('role')->default('student')->change();
        });

        // Migrate is_banned to status = suspended
        DB::table('users')->where('is_banned', true)->update(['status' => 'suspended']);
        
        // Clean up legacy role values if any (e.g., 'user' to 'student')
        DB::table('users')->where('role', 'user')->update(['role' => 'student']);

        Schema::table('users', function (Blueprint $table) {
            // Remove legacy is_banned column
            $table->dropColumn(['is_banned', 'ban_reason']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('password');
            $table->string('ban_reason')->nullable()->after('is_banned');
        });

        // Rollback status to is_banned
        DB::table('users')->where('status', 'suspended')->update(['is_banned' => true]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->string('role')->default('user')->change();
        });
    }
};
