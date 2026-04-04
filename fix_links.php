<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use App\Models\EducationContent;
use Illuminate\Support\Str;

echo "--- MATCHING ORPHANS ---\n";
$orphans = EducationContent::where('type', 'module')->whereNull('training_module_id')->get();
foreach ($orphans as $c) {
    // Try to find a matching module
    $module = TrainingModule::where('title', 'like', "%{$c->title}%")
        ->orWhere('title', 'like', "%" . str_replace('(VR)', '', $c->title) . "%")
        ->first();
    
    if ($module) {
        echo "Linking Content [ID: {$c->id}] '{$c->title}' to Module [ID: {$module->id}] '{$module->title}'\n";
        $c->update(['training_module_id' => $module->id]);
    } else {
        echo "No match for '{$c->title}'. Creating new module...\n";
        $newModule = TrainingModule::create([
            'title' => $c->title,
            'slug' => Str::slug($c->title),
            'description' => $c->description ?? 'Auto-generated from content.',
            'difficulty' => $c->level ?? 'Beginner',
            'estimated_duration' => '15 min',
            'is_active' => true,
        ]);
        $c->update(['training_module_id' => $newModule->id]);
        echo "  Created Module [ID: {$newModule->id}]\n";
    }
}

echo "\n--- ENSURING GUDANG HAS CONTENT ---\n";
$gudang = TrainingModule::where('slug', 'gudang')->first();
if ($gudang) {
    $hasContent = EducationContent::where('training_module_id', $gudang->id)->exists();
    if (!$hasContent) {
        echo "Creating primary content for 'Gudang'\n";
        EducationContent::create([
            'training_module_id' => $gudang->id,
            'title' => $gudang->title,
            'slug' => Str::slug($gudang->title) . '-main',
            'type' => 'module',
            'category' => 'Logistik',
            'level' => $gudang->difficulty,
            'description' => $gudang->description,
            'is_active' => true,
            'code' => 'EDU-' . strtoupper(Str::random(6)),
        ]);
    }
}
echo "Done\n";
