<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = app('router')->getRoutes();
$r = $routes->getByName('storage.local');

if ($r) {
    echo "URI: " . $r->uri() . "\n";
    echo "Methods: " . implode(',', $r->methods()) . "\n";
    echo "Action: " . json_encode($r->getAction()) . "\n";
} else {
    echo "Route 'storage.local' not found.\n";
}

// Also check all routes starting with storage/
echo "\nAll storage routes:\n";
foreach ($routes as $route) {
    if (strpos($route->uri(), 'storage/') === 0) {
        echo $route->method() . " " . $route->uri() . " -> " . ($route->getName() ?? 'unnamed') . "\n";
    }
}
