<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\News;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Admin\NewsController;
use Illuminate\Http\Request;

// Mock an admin user
$admin = \App\Models\User::where('email', 'admin@pharmvr.com')->first();
\Illuminate\Support\Facades\Auth::login($admin);

// Create a real image
$file = UploadedFile::fake()->image('test_news_real.jpg', 1200, 800);

$request = Request::create('/admin/news', 'POST', [
    'title' => 'Test News Upload',
    'content' => 'Test content for upload verification.',
    'status' => 'published',
    'category' => 'Test'
], [], ['image' => $file]);

$controller = app(NewsController::class);

try {
    // We need to bypass validation or mock it, but let's just use the trait directly to test storage
    $trait = new class { use \App\Traits\OptimizesImages; public function test($f, $d) { return $this->storeOptimized($f, $d); } };
    
    // Switch back to real public disk for actual check if needed, but let's see if trait works with fake first
    $path = $trait->test($file, 'news');
    echo "Uploaded Path: " . $path . "\n";
    
    // Check if file exists on 'public' disk
    if (Storage::disk('public')->exists($path)) {
        echo "File exists on public disk!\n";
    } else {
        echo "File NOT FOUND on public disk.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
