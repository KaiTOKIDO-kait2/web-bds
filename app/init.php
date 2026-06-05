<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

// hàm giúp đọc file .env và gán các biến cho $_ENV và $_SERVER . ví dụ CHATBOT_SERVICE_URL=http://127.0.0.1:8000
if (!function_exists('loadEnvFile')) {
    function loadEnvFile(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
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
// load file .env 
$envPath = dirname(__DIR__) . '/.env';
loadEnvFile($envPath);

require_once 'core/App.php';
require_once 'core/Controller.php';
require_once 'core/Database.php';
require_once 'core/WorkflowHelper.php';

if (!function_exists('detectBaseUrl')) {
    function detectBaseUrl(): string
    {
        $configuredBaseUrl = getenv('APP_BASE_URL');
        if ($configuredBaseUrl !== false && $configuredBaseUrl !== '') {
            return rtrim($configuredBaseUrl, '/');
        }

        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptName === '') {
            return '';
        }

        $baseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($baseUrl === '/' || $baseUrl === '.') {
            return '';
        }

        if (str_ends_with($baseUrl, '/public')) {
            $baseUrl = substr($baseUrl, 0, -7);
        }

        return $baseUrl;
    }
}

define('BASEURL', detectBaseUrl());

// Lấy BASE URL của chatbot
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
