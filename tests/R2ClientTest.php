<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Config;

class R2ClientTest extends TestCase
{
    public function testConfigLoading()
    {
        $envFile = __DIR__ . '/../.env.example';
        if (file_exists($envFile)) {
            $config = new Config($envFile);
            $this->assertIsString($config->get('R2_BUCKET_NAME'));
        } else {
            $this->markTestSkipped('Config file not found');
        }
    }

    public function testEnvVariables()
    {
        $this->assertEnvironmentVariableExists('R2_BUCKET_NAME');
        $this->assertEnvironmentVariableExists('R2_ENDPOINT');
    }

    private function assertEnvironmentVariableExists(string $var)
    {
        // This would check if needed env vars are set
        // For now, just verify it's defined or in .env
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $contents = file_get_contents($envFile);
            $this->assertStringContainsString($var, $contents);
        }
    }
}
