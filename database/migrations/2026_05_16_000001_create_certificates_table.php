<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('certificate_type', 100);
            $table->string('certificate_id', 120)->unique();
            $table->string('title', 255);
            $table->string('status', 50)->default('issued');
            $table->timestamp('issued_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'certificate_type']);
            $table->index(['certificate_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
