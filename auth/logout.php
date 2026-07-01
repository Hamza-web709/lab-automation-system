<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    if (current_user_id()) {
        logActivity(getPDO(), current_user_id(), 'logout', 'User signed out.');
    }
} catch (Throwable $exception) {
    // Logging should not block logout.
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
session_start();
set_flash('success', 'You have been signed out.');
redirect('auth/login.php');
