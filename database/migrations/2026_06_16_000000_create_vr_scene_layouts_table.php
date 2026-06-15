<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vr_scene_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('scene_slug')->index();
            $table->string('title')->nullable();
            $table->string('template_key')->nullable()->index();
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('draft')->index();
            $table->json('layout_json');
            $table->json('metadata_json')->nullable();
            $table->json('validation_warnings_json')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index(['scene_slug', 'status', 'version'], 'vr_scene_layouts_scene_status_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vr_scene_layouts');
    }
};
