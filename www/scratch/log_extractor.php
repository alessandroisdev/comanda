<?php

$logPath = __DIR__.'/../storage/logs/laravel-2026-06-01.log';
if (! file_exists($logPath)) {
    echo "Log file does not exist.\n";
    exit;
}

$pattern = '/\b\d{4}[- ]?\d{4}[- ]?\d{4}[- ]?\d{4}\b/';
$content = file_get_contents($logPath);
$lines = explode("\n", $content);

echo "--- MATCHING CREDIT CARD SUBSTRINGS ---\n";
foreach ($lines as $i => $line) {
    if (preg_match($pattern, $line, $matches)) {
        echo 'Line '.($i + 1).': Matched [ '.$matches[0].' ] in line: '.substr($line, 0, 150)."...\n";
    }
}
