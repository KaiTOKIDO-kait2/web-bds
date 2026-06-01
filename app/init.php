<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

if (!function_exists('logAppBootstrap')) {
    function logAppBootstrap(string $message): void
    {
        error_log('[RealEstate bootstrap] ' . $message);
    }
}

if (!function_exists('loadEnvFile')) {
    function loadEnvFile(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            logAppBootstrap('.env not found or not readable at: ' . $path);
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            logAppBootstrap('Failed to read .env at: ' . $path);
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if ($name === '') {
                continue;
            }

            $firstChar = substr($value, 0, 1);
            $lastChar = substr($value, -1);
            if (($firstChar === '"' && $lastChar === '"') || ($firstChar === "'" && $lastChar === "'")) {
                $value = substr($value, 1, -1);
            }

            if (getenv($name) !== false) {
                continue;
            }

            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

$envPath = dirname(__DIR__) . '/.env';
loadEnvFile($envPath);

$expectedEnvKeys = ['MYSQL_HOST', 'MYSQL_PORT', 'MYSQL_USER', 'MYSQL_PASSWORD', 'MYSQL_DATABASE'];
$missingEnvKeys = [];
foreach ($expectedEnvKeys as $envKey) {
    $value = getenv($envKey);
    if ($value === false || $value === '') {
        $missingEnvKeys[] = $envKey;
    }
}

if (!empty($missingEnvKeys)) {
    logAppBootstrap('Missing env keys after bootstrap: ' . implode(', ', $missingEnvKeys));
} else {
    logAppBootstrap('Bootstrap env loaded successfully from: ' . $envPath);
}

require_once 'core/App.php';
require_once 'core/Controller.php';
require_once 'core/Database.php';
require_once 'core/WorkflowHelper.php';

// Define base URL
define('BASEURL', '/Real-Estate-website-in-PHP-main');

// Chatbot microservice (FastAPI) — có thể ghi đè bằng biến môi trường hệ thống
if (!defined('CHATBOT_SERVICE_URL')) {
    $chatbotUrl = getenv('CHATBOT_SERVICE_URL');
    define(
        'CHATBOT_SERVICE_URL',
        ($chatbotUrl !== false && $chatbotUrl !== '') ? $chatbotUrl : 'http://127.0.0.1:8000'
    );
}
if (!defined('CHATBOT_INTERNAL_SECRET')) {
    $chatbotSecret = getenv('CHATBOT_INTERNAL_SECRET');
    define('CHATBOT_INTERNAL_SECRET', ($chatbotSecret !== false && $chatbotSecret !== '') ? $chatbotSecret : '');
}
