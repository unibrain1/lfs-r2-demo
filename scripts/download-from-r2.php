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

// Get arguments
$key = $argv[1] ?? null;
$savePath = $argv[2] ?? null;

if (!$key || !$savePath) {
    echo "Usage: php download-from-r2.php <key> <save-path>\n";
    echo "Example: php download-from-r2.php documents/sample.pdf ./downloaded.pdf\n";
    exit(1);
}

echo "Downloading $key from R2...\n";
if ($r2Client->downloadFile($key, $savePath)) {
    echo "✓ Download successful to $savePath\n";
} else {
    echo "✗ Download failed\n";
    exit(1);
}
