<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin', 'lab_manager']);
$pageTitle = 'Add Product Type';
$activePage = 'product-types';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Master data</p>
        <h2>Add Product Type</h2>
        <p>Product type codes become part of generated product IDs.</p>
    </div>
</div>
<form class="form-card surface-card" method="post" action="<?= url('admin/product-types/store.php') ?>" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="field">
            <label>Name <span class="required">*</span></label>
            <input type="text" name="name" required maxlength="120">
            <small class="field-error">Name is required.</small>
        </div>
        <div class="field">
            <label>Code <span class="required">*</span></label>
            <input type="text" name="code" required maxlength="10" placeholder="SWG">
            <small class="field-hint">Use up to 10 letters or numbers.</small>
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
        <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Save Type</button>
        <a class="btn btn-soft" href="<?= url('admin/product-types/index.php') ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</form>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
