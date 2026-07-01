<?php
$activePage = $activePage ?? 'dashboard';
$navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'href' => 'tester/dashboard.php'],
    ['key' => 'assigned-tests', 'label' => 'Assigned Tests', 'icon' => 'clipboard-check', 'href' => 'tester/assigned-tests.php'],
    ['key' => 'history', 'label' => 'My Test History', 'icon' => 'history', 'href' => 'tester/my-test-history.php'],
    ['key' => 'profile', 'label' => 'Profile', 'icon' => 'user-circle', 'href' => 'tester/profile.php'],
];
include __DIR__ . '/sidebar_base.php';
