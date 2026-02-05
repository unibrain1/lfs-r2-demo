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

// Get file path from command line
$filePath = $argv[1] ?? null;
if (!$filePath || !file_exists($filePath)) {
    echo "Usage: php upload-to-r2.php <file-path>\n";
    echo "Example: php upload-to-r2.php ./documents/sample.pdf\n";
    exit(1);
}

$fileName = basename($filePath);
$key = 'documents/' . $fileName;

echo "Uploading $fileName to R2...\n";
$result = $r2Client->uploadFile($filePath, $key);

if ($result['success']) {
    echo "✓ Upload successful!\n";
    echo "Key: {$result['key']}\n";
    echo "Public URL: {$result['url']}\n";
    echo "ETag: {$result['etag']}\n";
} else {
    echo "✗ Upload failed: {$result['error']}\n";
    exit(1);
}
