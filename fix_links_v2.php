<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use App\Models\EducationContent;
use Illuminate\Support\Str;

echo "--- LINKING ORPHANS ---\n";
$orphans = EducationContent::where('type', 'module')->whereNull('training_module_id')->get();
foreach ($orphans as $c) {
    // Try to find a matching module by title
    $module = TrainingModule::where('title', $c->title)->first();
    
    if ($module) {
        echo "Linking Content [ID: {$c->id}] '{$c->title}' -> Module [ID: {$module->id}]\n";
        $c->update(['training_module_id' => $module->id]);
    } else {
        echo "No exact match for '{$c->title}'. Checking for similar titles...\n";
        $similar = TrainingModule::where('title', 'like', '%' . substr($c->title, 0, 10) . '%')->first();
        if ($similar) {
            echo "  Found similar: '{$similar->title}'. Linking...\n";
            $c->update(['training_module_id' => $similar->id]);
        } else {
            echo "  No similar found. Creating new TrainingModule...\n";
            $slug = Str::slug($c->title);
            // Check if slug exists
            if (TrainingModule::where('slug', $slug)->exists()) {
                $slug .= '-' . rand(100, 999);
            }
            
            $m = TrainingModule::create([
                'title' => $c->title,
                'slug' => $slug,
                'description' => $c->description ?? 'Auto-generated.',
                'difficulty' => $c->level ?? 'Beginner',
                'estimated_duration' => '15 min',
                'is_active' => true,
            ]);
            $c->update(['training_module_id' => $m->id]);
            echo "  Created and Linked to Module [ID: {$m->id}]\n";
        }
    }
}

echo "\n--- GUDANG CHECK ---\n";
$gudang = TrainingModule::where('slug', 'gudang')->first();
if ($gudang) {
    $hasContent = EducationContent::where('training_module_id', $gudang->id)->exists();
    if (!$hasContent) {
        echo "Creating primary content for 'Gudang'\n";
        EducationContent::create([
            'training_module_id' => $gudang->id,
            'title' => $gudang->title,
            'slug' => 'gudang-main-' . rand(100, 999),
            'type' => 'module',
            'category' => 'Gudang',
            'level' => 'Beginner',
            'description' => $gudang->description,
            'is_active' => true,
            'code' => 'EDU-' . strtoupper(Str::random(6)),
        ]);
    } else {
        echo "Gudang already has content.\n";
    }
}
echo "Done\n";
