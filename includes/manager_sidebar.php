<?php
$activePage = $activePage ?? 'dashboard';
$navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'href' => 'lab-manager/dashboard.php'],
    ['key' => 'products', 'label' => 'Products', 'icon' => 'package', 'href' => 'lab-manager/assigned-products.php'],
    ['key' => 'tests', 'label' => 'Testing Records', 'icon' => 'clipboard-check', 'href' => 'lab-manager/testing-records.php'],
    ['key' => 'create-test', 'label' => 'Create Test', 'icon' => 'clipboard-plus', 'href' => 'lab-manager/create-test.php'],
    ['key' => 'progress', 'label' => 'Test Progress', 'icon' => 'activity', 'href' => 'lab-manager/update-test-status.php'],
    ['key' => 'reports', 'label' => 'Reports', 'icon' => 'file-bar-chart', 'href' => 'lab-manager/reports.php'],
    ['key' => 'advanced-search', 'label' => 'Advanced Search', 'icon' => 'search', 'href' => 'admin/search/advanced-search.php'],
    ['key' => 'profile', 'label' => 'Profile', 'icon' => 'user-circle', 'href' => 'lab-manager/profile.php'],
];
include __DIR__ . '/sidebar_base.php';
