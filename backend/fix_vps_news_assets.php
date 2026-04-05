<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$updates = [
    1 => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&q=80&w=1470',
    2 => 'https://www.pom.go.id/storage/shares/Berita/2026/03/IMG_20260318_104618_675.jpg?itok=ZpL_KXp9',
    3 => 'https://images.unsplash.com/photo-1576091160550-217359f48f4c?auto=format&fit=crop&q=80&w=1470',
    4 => 'https://images.unsplash.com/photo-1587854692152-cbe660dbbb88?auto=format&fit=crop&q=80&w=1470',
    11 => 'https://images.unsplash.com/photo-1512069772995-ec65ed45afd6?auto=format&fit=crop&q=80&w=1470',
    41 => 'https://images.unsplash.com/photo-1532009835281-99cd71550118?auto=format&fit=crop&q=80&w=1470',
];

foreach ($updates as $id => $url) {
    $news = \App\Models\News::find($id);
    if ($news) {
        $news->update([
            'image_url' => $url,
            'thumbnail' => $url
        ]);
        echo "Updated News ID {$id}\n";
    }
}
