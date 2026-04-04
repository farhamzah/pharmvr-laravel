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
            $table->foreignId('training_module_id')->nullable()->after('type')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education_contents', function (Blueprint $table) {
            $table->dropForeign(['training_module_id']);
            $table->dropColumn('training_module_id');
        });
    }
};
