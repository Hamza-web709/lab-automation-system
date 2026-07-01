<?php
// Application bootstrap: sessions, timezone, and URL path detection live here.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Karachi');

define('APP_NAME', 'Smart Lab Automation System');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true);

$appRoot = str_replace('\\', '/', realpath(dirname(__DIR__)));
$documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__)));
$basePath = '';

// Detect the folder name under htdocs so the project works as /lab-automation or /lab-automation-system.
if ($documentRoot && strpos($appRoot, $documentRoot) === 0) {
    $basePath = substr($appRoot, strlen($documentRoot));
}

$basePath = '/' . trim(str_replace('\\', '/', $basePath), '/');
if ($basePath === '/') {
    $basePath = '';
}

define('APP_BASE_PATH', $basePath);
