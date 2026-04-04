<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    // We cannot easily list all tables without knowing the driver, but assuming SQLite/MySQL
    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
    if (empty($tables)) {
         $tables = DB::select('SHOW TABLES');
         $key = 'Tables_in_' . DB::getDatabaseName();
    } else {
        $key = 'name';
    }

    foreach ($tables as $table) {
        $tableName = $table->$key;
        if (str_starts_with($tableName, 'sqlite_')) continue;
        echo "Table: $tableName\n";
        $columns = Schema::getColumnListing($tableName);
        foreach ($columns as $column) {
            echo "  - $column\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
