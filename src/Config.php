<?php

namespace App;

class Config
{
    private array $config = [];

    public function __construct(string $envPath = '.env')
    {
        $this->loadEnv($envPath);
    }

    private function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Environment file not found: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2) + [null, null];
            if ($key) {
                $this->config[trim($key)] = trim($value);
            }
        }
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->config;
    }
}
