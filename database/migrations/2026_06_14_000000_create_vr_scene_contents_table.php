<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vr_scene_contents', function (Blueprint $table) {
            $table->id();
            $table->string('scene_slug')->index();
            $table->string('content_key')->index();
            $table->string('content_type')->index();
            $table->string('locale', 12)->default('id')->index();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('body')->nullable();
            $table->json('items_json')->nullable();
            $table->json('metadata_json')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->string('status')->default('published')->index();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['scene_slug', 'content_key', 'locale', 'version'], 'vr_scene_contents_unique_version');
            $table->index(['scene_slug', 'content_key', 'locale', 'is_active', 'status'], 'vr_scene_contents_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vr_scene_contents');
    }
};
