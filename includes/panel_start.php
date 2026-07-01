<?php
require_once __DIR__ . '/role_check.php';
$pageTitle = $pageTitle ?? 'Dashboard';
$activePage = $activePage ?? 'dashboard';
$bodyClass = trim(($bodyClass ?? '') . ' app-body');
$panelName = $panelName ?? 'Panel';
$panelBrand = $panelBrand ?? 'LabFlow';
include __DIR__ . '/header.php';

$sidebarFile = match (current_user()['role'] ?? 'admin') {
    'lab_manager' => __DIR__ . '/manager_sidebar.php',
    'tester' => __DIR__ . '/tester_sidebar.php',
    default => __DIR__ . '/admin_sidebar.php',
};
include $sidebarFile;
?>
<div class="app-shell">
    <div class="app-main">
        <?php include __DIR__ . '/panel_topbar.php'; ?>
        <main class="content">
            <?php include __DIR__ . '/flash.php'; ?>
