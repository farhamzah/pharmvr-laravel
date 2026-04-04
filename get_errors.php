<?php
$lines = file('storage/logs/laravel.log');
$errors = [];
$capture = false;
$currentError = "";

foreach ($lines as $line) {
    if (strpos($line, 'local.ERROR') !== false) {
        if ($currentError !== "") {
            $errors[] = $currentError;
        }
        $currentError = $line;
        $capture = true;
    } elseif ($capture) {
        if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] local\./', $line)) {
            $errors[] = $currentError;
            $currentError = "";
            $capture = false;
        } else {
            $currentError .= $line;
        }
    }
}
if ($currentError !== "") {
    $errors[] = $currentError;
}

$lastErrors = array_slice($errors, -3);
$out = "";
foreach ($lastErrors as $err) {
    $out .= "====================\n" . substr($err, 0, 1500) . "\n...\n";
}
file_put_contents('output_errors_fixed.txt', $out);
