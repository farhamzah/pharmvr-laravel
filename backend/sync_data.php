<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use App\Models\EducationContent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

echo "--- DATA SYNCHRONIZATION ---\n";

// 1. Link Orphans
$orphans = EducationContent::where('type', 'Module')->whereNull('training_module_id')->get();
foreach ($orphans as $c) {
    $module = TrainingModule::where('title', $c->title)->first();
    if ($module) {
        echo "Linking [ID: {$c->id}] '{$c->title}' -> Module ID: {$module->id}\n";
        $c->training_module_id = $module->id;
        $c->save();
    } else {
        echo "Creating TrainingModule for '{$c->title}'\n";
        $slug = Str::slug($c->title);
        if (TrainingModule::where('slug', $slug)->exists()) $slug .= '-' . rand(100, 999);
        
        try {
            $m = TrainingModule::create([
                'title' => $c->title,
                'slug' => $slug,
                'description' => $c->description ?? 'Auto-generated.',
                'difficulty' => in_array($c->level, ['Beginner', 'Intermediate', 'Advanced']) ? $c->level : 'Beginner',
                'estimated_duration' => '30 min',
                'is_active' => true,
            ]);
            $c->training_module_id = $m->id;
            $c->save();
            echo "  Created Module ID: {$m->id}\n";
        } catch (\Exception $e) {
            echo "  Error creating module for '{$c->title}': " . $e->getMessage() . "\n";
        }
    }
}

// 2. Ensure all TrainingModules have at least one EducationContent
foreach (TrainingModule::all() as $m) {
    if (!EducationContent::where('training_module_id', $m->id)->exists()) {
        echo "Creating primary content for Module '{$m->title}' (ID: {$m->id})\n";
        try {
            EducationContent::create([
                'training_module_id' => $m->id,
                'title' => $m->title,
                'slug' => Str::slug($m->title) . '-main-' . rand(100, 999),
                'type' => 'Module',
                'category' => 'Training',
                'level' => $m->difficulty,
                'description' => $m->description,
                'is_active' => true,
                'code' => 'EDU-' . strtoupper(Str::random(6)),
            ]);
        } catch (\Exception $e) {
            echo "  Error: " . $e->getMessage() . "\n";
        }
    }
}

// 3. Fix missing thumbnails for old content by copying from TrainingModule if available
foreach (EducationContent::where('type', 'Module')->get() as $c) {
    if ($c->trainingModule && $c->trainingModule->cover_image_path && empty($c->thumbnail_url)) {
        echo "Updating Content [ID: {$c->id}] thumbnail from TrainingModule\n";
        $c->thumbnail_url = $c->trainingModule->cover_image_path;
        $c->save();
    }
}

echo "Synchronization Complete.\n";
