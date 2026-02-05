#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Config;
use App\R2Client;

// Load configuration
$config = new Config(__DIR__ . '/../.env');

// Initialize R2 client
$r2Client = new R2Client(
    $config->get('R2_BUCKET_NAME'),
    $config->get('R2_PUBLIC_ENDPOINT'),
    [
        'endpoint' => $config->get('R2_ENDPOINT'),
        'key' => $config->get('R2_ACCESS_KEY_ID'),
        'secret' => $config->get('R2_SECRET_ACCESS_KEY'),
        'region' => $config->get('R2_REGION', 'auto'),
    ]
);

$prefix = $argv[1] ?? 'documents/';

echo "Listing files in R2 (prefix: $prefix)...\n";
$files = $r2Client->listFiles($prefix);

if (empty($files)) {
    echo "No files found.\n";
    exit(0);
}

echo "\n";
printf("%-40s %12s  %s\n", "File", "Size", "Modified");
echo str_repeat("-", 80) . "\n";

foreach ($files as $file) {
    printf("%-40s %12s  %s\n", substr($file['key'], 0, 40), $file['size'] . ' B', $file['modified']);
}

echo "\n";
