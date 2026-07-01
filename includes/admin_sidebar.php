<?php
$activePage = $activePage ?? 'dashboard';
$navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'href' => 'admin/dashboard.php'],
    ['key' => 'users', 'label' => 'Users', 'icon' => 'users', 'href' => 'admin/users/index.php'],
    ['key' => 'product-types', 'label' => 'Product Types', 'icon' => 'boxes', 'href' => 'admin/product-types/index.php'],
    ['key' => 'products', 'label' => 'Products', 'icon' => 'package', 'href' => 'admin/products/index.php'],
    ['key' => 'testing-departments', 'label' => 'Testing Departments', 'icon' => 'building-2', 'href' => 'admin/testing-departments/index.php'],
    ['key' => 'testing-types', 'label' => 'Testing Types', 'icon' => 'flask-conical', 'href' => 'admin/testing-types/index.php'],
    ['key' => 'tests', 'label' => 'Testing Records', 'icon' => 'clipboard-check', 'href' => 'admin/tests/index.php'],
    ['key' => 'advanced-search', 'label' => 'Advanced Search', 'icon' => 'search', 'href' => 'admin/search/advanced-search.php'],
    ['key' => 'reports', 'label' => 'Reports', 'icon' => 'file-bar-chart', 'href' => 'admin/reports/index.php'],
    ['key' => 'profile', 'label' => 'Profile', 'icon' => 'user-circle', 'href' => 'admin/profile.php'],
];
include __DIR__ . '/sidebar_base.php';
