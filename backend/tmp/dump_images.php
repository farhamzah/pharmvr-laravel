<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\News;
use App\Models\EducationContent;
use App\Models\UserProfile;

echo "--- NEWS ---\n";
foreach(News::all() as $n) {
    echo "Title: {$n->title} | Image: " . ($n->image_url ?? 'NULL') . "\n";
}

echo "\n--- EDUCATION ---\n";
foreach(EducationContent::all() as $ec) {
    echo "Title: {$ec->title} | Type: {$ec->type} | Thumbnail: " . ($ec->thumbnail_url ?? 'NULL') . "\n";
}

echo "\n--- USERS ---\n";
foreach(UserProfile::all() as $up) {
    echo "User: {$up->user->name} | Avatar: " . ($up->avatar_url ?? 'NULL') . "\n";
}
