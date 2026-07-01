<?php
require_once __DIR__ . '/role_check.php';

requireLogin();

// Existing /admin pages are reserved for administrators in the new role-based system.
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
if (str_contains($script, '/admin/')) {
    requireRole('admin');
}
