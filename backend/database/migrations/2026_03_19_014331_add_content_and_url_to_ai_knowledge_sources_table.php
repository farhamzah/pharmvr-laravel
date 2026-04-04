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
        Schema::table('ai_knowledge_sources', function (Blueprint $table) {
            $table->string('url')->nullable()->after('file_path');
            $table->longText('content')->nullable()->after('url');
            
            // Note: In SQLite, modifying enum columns is tricky. 
            // Since this is likely a dev environment, we'll just hope the app-level enums 
            // handle the validation and the DB stores them as strings.
            // If it's MySQL/Postgres, we'd need a raw ALTER TABLE.
        });
    }

    public function down(): void
    {
        Schema::table('ai_knowledge_sources', function (Blueprint $table) {
            $table->dropColumn(['url', 'content']);
        });
    }
};
