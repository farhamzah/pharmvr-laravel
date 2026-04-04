<?php

use Illuminate\Support\Facades\Artisan;

try {
    Artisan::call('migrate:fresh');
    echo "Migration fresh: OK\n";
    Artisan::call('db:seed', ['--class' => 'UserSeeder']);
    echo "UserSeeder: OK\n";
    Artisan::call('db:seed', ['--class' => 'ContentSeeder']);
    echo "ContentSeeder: OK\n";
    Artisan::call('db:seed', ['--class' => 'AssessmentSeeder']);
    echo "AssessmentSeeder: OK\n";
    Artisan::call('db:seed', ['--class' => 'VrSeeder']);
    echo "VrSeeder: OK\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
