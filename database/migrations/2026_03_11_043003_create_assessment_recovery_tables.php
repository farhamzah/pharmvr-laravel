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
        // 1. Ensure assessment_attempts exists
        if (!Schema::hasTable('assessment_attempts')) {
            Schema::create('assessment_attempts', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        // 2. Add columns to assessment_attempts if missing
        Schema::table('assessment_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('assessment_attempts', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('assessment_attempts', 'assessment_id')) {
                $table->unsignedBigInteger('assessment_id')->nullable();
            }
            if (!Schema::hasColumn('assessment_attempts', 'score')) {
                $table->integer('score')->nullable();
            }
            if (!Schema::hasColumn('assessment_attempts', 'status')) {
                $table->enum('status', ['in_progress', 'completed', 'failed'])->default('in_progress');
            }
            if (!Schema::hasColumn('assessment_attempts', 'started_at')) {
                $table->timestamp('started_at')->nullable();
            }
            if (!Schema::hasColumn('assessment_attempts', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
        });

        // 3. Ensure user_answers exists
        if (!Schema::hasTable('user_answers')) {
            Schema::create('user_answers', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        // 4. Add columns to user_answers if missing
        Schema::table('user_answers', function (Blueprint $table) {
            if (!Schema::hasColumn('user_answers', 'assessment_attempt_id')) {
                $table->foreignId('assessment_attempt_id')->nullable()->constrained('assessment_attempts')->onDelete('cascade');
            }
            if (!Schema::hasColumn('user_answers', 'question_id')) {
                $table->unsignedBigInteger('question_id')->nullable();
            }
            if (!Schema::hasColumn('user_answers', 'option_id')) {
                $table->unsignedBigInteger('option_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_answers');
        Schema::dropIfExists('assessment_attempts');
    }
};
