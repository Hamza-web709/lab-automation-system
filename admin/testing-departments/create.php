<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin', 'lab_manager']);
$pageTitle = 'Add Testing Department';
$activePage = 'testing-departments';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Lab structure</p>
        <h2>Add Testing Department</h2>
        <p>Department codes are used to organize test definitions and reports.</p>
    </div>
</div>
<form class="form-card surface-card" method="post" action="<?= url('admin/testing-departments/store.php') ?>" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="field">
            <label>Name <span class="required">*</span></label>
            <input type="text" name="name" required maxlength="120">
            <small class="field-error">Name is required.</small>
        </div>
        <div class="field">
            <label>Code <span class="required">*</span></label>
            <input type="text" name="code" required maxlength="10" placeholder="ELEC">
            <small class="field-error">Code is required.</small>
        </div>
        <div class="field span-2">
            <label>Description</label>
            <textarea name="description"></textarea>
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
        <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Save Department</button>
        <a class="btn btn-soft" href="<?= url('admin/testing-departments/index.php') ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</form>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
