<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin']);
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute(['id' => $id]);
$user = $stmt->fetch();
if (!$user) {
    set_flash('error', 'User not found.');
    redirect('admin/users/index.php');
}
$pageTitle = 'Edit User';
$activePage = 'users';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Access control</p>
        <h2>Edit User</h2>
        <p>Leave password blank to keep the current password.</p>
    </div>
</div>
<form class="form-card surface-card" method="post" action="<?= url('admin/users/update.php') ?>" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
    <div class="form-grid">
        <div class="field">
            <label>Name <span class="required">*</span></label>
            <input type="text" name="name" value="<?= e($user['name']) ?>" required maxlength="120">
        </div>
        <div class="field">
            <label>Username <span class="required">*</span></label>
            <input type="text" name="username" value="<?= e($user['username']) ?>" required minlength="3" maxlength="30" pattern="[a-zA-Z0-9_.]{3,30}">
            <small class="field-hint">Letters, numbers, underscore, and dot only. No spaces.</small>
        </div>
        <div class="field">
            <label>Email <span class="required">*</span></label>
            <input type="email" name="email" value="<?= e($user['email']) ?>" required maxlength="160">
        </div>
        <div class="field">
            <label>New Password</label>
            <input type="password" name="password" minlength="6">
        </div>
        <div class="field">
            <label>Role</label>
            <select name="role">
                <?php foreach (['tester', 'lab_manager', 'admin'] as $item): ?>
                    <option value="<?= e($item) ?>" <?= optionSelected($user['role'], $item) ?>><?= e(humanize($item)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Status</label>
            <select name="status">
                <option value="active" <?= optionSelected($user['status'], 'active') ?>>Active</option>
                <option value="inactive" <?= optionSelected($user['status'], 'inactive') ?>>Inactive</option>
            </select>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Update User</button>
        <a class="btn btn-soft" href="<?= url('admin/users/index.php') ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</form>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
