<?php
$activePage = $activePage ?? 'dashboard';
$navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'href' => 'admin/dashboard.php'],
    ['key' => 'products', 'label' => 'Products', 'icon' => 'package', 'href' => 'admin/products/index.php'],
    ['key' => 'product-types', 'label' => 'Product Types', 'icon' => 'boxes', 'href' => 'admin/product-types/index.php'],
    ['key' => 'testing-departments', 'label' => 'Testing Departments', 'icon' => 'building-2', 'href' => 'admin/testing-departments/index.php'],
    ['key' => 'testing-types', 'label' => 'Testing Types', 'icon' => 'flask-conical', 'href' => 'admin/testing-types/index.php'],
    ['key' => 'tests', 'label' => 'Testing Records', 'icon' => 'clipboard-check', 'href' => 'admin/tests/index.php'],
    ['key' => 'advanced-search', 'label' => 'Advanced Search', 'icon' => 'search', 'href' => 'admin/search/advanced-search.php'],
    ['key' => 'reports', 'label' => 'Reports', 'icon' => 'file-bar-chart', 'href' => 'admin/reports/index.php'],
    ['key' => 'users', 'label' => 'Users', 'icon' => 'users', 'href' => 'admin/users/index.php'],
];
?>
<aside class="sidebar" id="sidebar">
    <a class="brand" href="<?= url('admin/dashboard.php') ?>">
        <span class="brand-mark"><i data-lucide="zap"></i></span>
        <span>
            <strong>LabFlow</strong>
            <small>Automation</small>
        </span>
    </a>

    <nav class="sidebar-nav" aria-label="Main navigation">
        <?php foreach ($navItems as $item): ?>
            <a class="nav-link <?= $activePage === $item['key'] ? 'is-active' : '' ?>" href="<?= url($item['href']) ?>">
                <i data-lucide="<?= e($item['icon']) ?>"></i>
                <span><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <a class="nav-link nav-link-logout" href="<?= url('auth/logout.php') ?>">
        <i data-lucide="log-out"></i>
        <span>Logout</span>
    </a>
</aside>
<div class="sidebar-backdrop" data-sidebar-close></div>
