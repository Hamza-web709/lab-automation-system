<?php
require_once __DIR__ . '/app.php';

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'lab_automation_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        // PDO is configured for exceptions and real prepared statements for safer database work.
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);
    } catch (PDOException $exception) {
        if (APP_DEBUG) {
            throw new RuntimeException('Database connection failed: ' . $exception->getMessage());
        }

        throw new RuntimeException('Database connection failed. Please contact the administrator.');
    }

    return $pdo;
}
