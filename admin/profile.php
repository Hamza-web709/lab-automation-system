<?php
require_once __DIR__ . '/../includes/auth_check.php';
$pageTitle = 'Profile';
$activePage = 'profile';
$user = current_user();
include __DIR__ . '/../includes/admin_start.php';
?>
<section class="form-card surface-card" data-animate="fade-up">
    <p class="eyebrow">Account</p>
    <h2><?= e($user['name']) ?></h2>
    <div class="report-grid">
        <div class="report-field"><span>Username</span><strong>@<?= e($user['username'] ?: '-') ?></strong></div>
        <div class="report-field"><span>Email</span><strong><?= e($user['email']) ?></strong></div>
        <div class="report-field"><span>Role</span><strong><?= e(humanize($user['role'])) ?></strong></div>
        <div class="report-field"><span>Status</span><strong><?= e(humanize($user['status'] ?? 'active')) ?></strong></div>
    </div>
</section>
<?php include __DIR__ . '/../includes/admin_end.php'; ?>
