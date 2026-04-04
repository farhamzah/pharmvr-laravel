<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$emails = \App\Models\User::pluck('email')->toArray();
foreach($emails as $email) {
    if (strpos($email, 'sri') !== false || strpos($email, 'widod') !== false) {
        echo "Found similar: " . $email . "\n";
    }
}
