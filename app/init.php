<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

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
