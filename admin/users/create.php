<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin']);
$pageTitle = 'Add User';
$activePage = 'users';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Access control</p>
        <h2>Add User</h2>
        <p>Create a secure account for a lab team member.</p>
    </div>
</div>
<form class="form-card surface-card" method="post" action="<?= url('admin/users/store.php') ?>" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="field">
            <label>Name <span class="required">*</span></label>
            <input type="text" name="name" required maxlength="120">
        </div>
        <div class="field">
            <label>Username <span class="required">*</span></label>
            <input type="text" name="username" required minlength="3" maxlength="30" pattern="[a-zA-Z0-9_.]{3,30}" placeholder="tester_01">
            <small class="field-hint">Letters, numbers, underscore, and dot only. No spaces.</small>
        </div>
        <div class="field">
            <label>Email <span class="required">*</span></label>
            <input type="email" name="email" required maxlength="160">
        </div>
        <div class="field">
            <label>Password <span class="required">*</span></label>
            <input type="password" name="password" required minlength="6">
            <small class="field-hint">Use at least 6 characters.</small>
        </div>
        <div class="field">
            <label>Role</label>
            <select name="role">
                <option value="tester">Tester</option>
                <option value="lab_manager">Lab Manager</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="field">
            <label>Status</label>
            <select name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Save User</button>
        <a class="btn btn-soft" href="<?= url('admin/users/index.php') ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</form>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
