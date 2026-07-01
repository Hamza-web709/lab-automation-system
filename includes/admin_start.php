<?php
require_once __DIR__ . '/auth_check.php';
$pageTitle = $pageTitle ?? 'Dashboard';
$activePage = $activePage ?? 'dashboard';
$panelName = 'Admin Panel';
$panelBrand = 'LabFlow';
$bodyClass = trim(($bodyClass ?? '') . ' app-body');
include __DIR__ . '/header.php';
include __DIR__ . '/admin_sidebar.php';
?>
<div class="app-shell">
    <div class="app-main">
        <?php include __DIR__ . '/panel_topbar.php'; ?>
        <main class="content">
            <?php include __DIR__ . '/flash.php'; ?>
