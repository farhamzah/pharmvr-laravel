<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$contents = \App\Models\EducationContent::all();
foreach($contents as $c) {
    echo "ID: {$c->id} | Slug: {$c->slug} | Code: {$c->code} | TM_ID: {$c->training_module_id}\n";
}
