<?php
$lines = file('storage/logs/laravel.log');
$lastLines = array_slice($lines, -100);
file_put_contents('error_tail.txt', implode("", $lastLines));
