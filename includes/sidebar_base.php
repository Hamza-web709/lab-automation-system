<?php $panelBrand = $panelBrand ?? 'LabFlow'; ?>
<aside class="sidebar" id="sidebar">
    <a class="brand" href="<?= url(dashboardPathForRole(current_user()['role'] ?? 'admin')) ?>">
        <span class="brand-mark"><i data-lucide="zap"></i></span>
        <span>
            <strong><?= e($panelBrand) ?></strong>
            <small><?= e(humanize(current_user()['role'] ?? 'admin')) ?></small>
        </span>
    </a>

    <nav class="sidebar-nav" aria-label="Panel navigation">
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
