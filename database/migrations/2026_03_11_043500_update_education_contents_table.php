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
            $table->string('category')->nullable()->after('type'); // e.g., 'GMP', 'CPOB', 'Sanitation'
            $table->string('level')->default('Beginner')->after('category'); // e.g., 'Beginner', 'Advanced'
            $table->json('tags')->nullable()->after('level');
            
            $table->index('category');
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education_contents', function (Blueprint $table) {
            $table->dropColumn(['category', 'level', 'tags']);
        });
    }
};
