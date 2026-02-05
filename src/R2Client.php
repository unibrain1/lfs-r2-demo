<?php

namespace App;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class R2Client
{
    private S3Client $s3Client;
    private string $bucketName;
    private string $publicEndpoint;

    public function __construct(string $bucketName, string $publicEndpoint, array $config = [])
    {
        $this->bucketName = $bucketName;
        $this->publicEndpoint = $publicEndpoint;

        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => $config['region'] ?? 'auto',
            'endpoint' => $config['endpoint'],
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ],
        ]);
    }

    /**
     * Upload a file to R2
     */
    public function uploadFile(string $filePath, string $key): array
    {
        try {
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
                'Body' => fopen($filePath, 'r'),
                'ContentType' => $this->getContentType($filePath),
            ]);

            return [
                'success' => true,
                'key' => $key,
                'url' => $this->getPublicUrl($key),
                'etag' => $result['ETag'],
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Download a file from R2
     */
    public function downloadFile(string $key, string $savePath): bool
    {
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);

            file_put_contents($savePath, $result['Body']);
            return true;
        } catch (AwsException $e) {
            echo "Error downloading file: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Generate a signed URL for temporary access
     */
    public function generateSignedUrl(string $key, int $expirationSeconds = 3600): string
    {
        try {
            $cmd = $this->s3Client->getCommand('GetObject', [
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);

            $request = $this->s3Client->createPresignedRequest($cmd, "+{$expirationSeconds} seconds");
            $presignedUrl = (string)$request->getUri();

            return $presignedUrl;
        } catch (AwsException $e) {
            echo "Error generating signed URL: " . $e->getMessage() . "\n";
            return '';
        }
    }

    /**
     * Get the public URL for a file
     */
    public function getPublicUrl(string $key): string
    {
        return rtrim($this->publicEndpoint, '/') . '/' . ltrim($key, '/');
    }

    /**
     * List files in R2
     */
    public function listFiles(string $prefix = ''): array
    {
        try {
            $result = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucketName,
                'Prefix' => $prefix,
            ]);

            $files = [];
            foreach ($result->get('Contents', []) as $object) {
                $files[] = [
                    'key' => $object['Key'],
                    'size' => $object['Size'],
                    'modified' => $object['LastModified']->format('Y-m-d H:i:s'),
                    'url' => $this->getPublicUrl($object['Key']),
                ];
            }

            return $files;
        } catch (AwsException $e) {
            echo "Error listing files: " . $e->getMessage() . "\n";
            return [];
        }
    }

    /**
     * Delete a file from R2
     */
    public function deleteFile(string $key): bool
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);

            return true;
        } catch (AwsException $e) {
            echo "Error deleting file: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Get MIME type for a file
     */
    private function getContentType(string $filePath): string
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }
}
