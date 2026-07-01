<?php
$user = current_user();
$userName = $_SESSION['user_name'] ?? ($user['name'] ?? 'User');
$username = $_SESSION['username'] ?? ($user['username'] ?? 'user');
$role = $_SESSION['role'] ?? ($_SESSION['user_role'] ?? ($user['role'] ?? 'role'));
$roleLabel = humanize($role);
$profilePath = match ($role) {
    'admin' => 'admin/profile.php',
    'lab_manager' => 'lab-manager/profile.php',
    'tester' => 'tester/profile.php',
    default => 'auth/login.php',
};
$profileLink = url($profilePath);
$logoutLink = url('auth/logout.php');
?>
<header class="topbar">
    <button class="icon-btn mobile-menu" type="button" aria-label="Open navigation" data-sidebar-toggle>
        <i data-lucide="menu"></i>
    </button>

    <div>
        <p class="eyebrow">Internal Laboratory</p>
        <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
    </div>

    <div class="topbar-actions">
        <button class="theme-toggle" type="button" aria-label="Switch to dark theme" aria-pressed="false" data-theme-toggle>
            <span class="theme-toggle-track">
                <span class="theme-toggle-thumb">
                    <i class="theme-icon theme-icon-sun" data-lucide="sun"></i>
                    <i class="theme-icon theme-icon-moon" data-lucide="moon"></i>
                </span>
            </span>
            <span class="theme-toggle-text" data-theme-label>Light</span>
        </button>

        <div class="user-dropdown">
            <button type="button" class="user-dropdown-toggle" id="userDropdownToggle" aria-haspopup="true" aria-expanded="false">
                <span class="user-avatar">
                    <?= e(strtoupper(substr($userName, 0, 1))) ?>
                </span>

                <span class="user-meta">
                    <strong><?= e($userName) ?></strong>
                    <small>@<?= e($username ?: 'user') ?></small>
                    <span class="role-badge"><?= e($roleLabel) ?></span>
                </span>

                <span class="dropdown-chevron">⌄</span>
            </button>

            <div class="user-dropdown-menu" id="userDropdownMenu">
                <a href="<?= e($profileLink) ?>" class="dropdown-item">
                    Profile
                </a>
                <a href="<?= e($logoutLink) ?>" class="dropdown-item logout">
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>
