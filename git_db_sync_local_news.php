<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$news = \App\Models\News::whereIn('id', [1, 2, 3, 4, 11, 41])->get(['id', 'image_url'])->toArray();
foreach ($news as $n) {
    echo $n['id'] . '|' . $n['image_url'] . "\n";
}
