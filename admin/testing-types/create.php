<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin', 'lab_manager']);
$pdo = getPDO();
$departments = $pdo->query('SELECT id, name FROM testing_departments WHERE status = "active" ORDER BY name')->fetchAll();
$productTypes = $pdo->query('SELECT id, name FROM product_types WHERE status = "active" ORDER BY name')->fetchAll();
$pageTitle = 'Add Testing Type';
$activePage = 'testing-types';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Testing setup</p>
        <h2>Add Testing Type</h2>
        <p>Reusable test criteria speeds up future testing record entry.</p>
    </div>
</div>
<form class="form-card surface-card" method="post" action="<?= url('admin/testing-types/store.php') ?>" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="field">
            <label>Department <span class="required">*</span></label>
            <select name="department_id" required>
                <option value="">Select department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= (int) $department['id'] ?>"><?= e($department['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <small class="field-error">Department is required.</small>
        </div>
        <div class="field">
            <label>Product Type <span class="required">*</span></label>
            <select name="product_type_id" required>
                <option value="">Select product type</option>
                <?php foreach ($productTypes as $type): ?>
                    <option value="<?= (int) $type['id'] ?>"><?= e($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <small class="field-error">Product type is required.</small>
        </div>
        <div class="field">
            <label>Name <span class="required">*</span></label>
            <input type="text" name="name" required maxlength="160">
            <small class="field-error">Name is required.</small>
        </div>
        <div class="field">
            <label>Testing Code <span class="required">*</span></label>
            <input type="text" name="testing_code" required maxlength="10" placeholder="VOLT">
            <small class="field-error">Testing code is required.</small>
        </div>
        <div class="field span-2">
            <label>Description</label>
            <textarea name="description"></textarea>
        </div>
        <div class="field span-2">
            <label>Criteria</label>
            <textarea name="criteria"></textarea>
        </div>
        <div class="field span-2">
            <label>Expected Output</label>
            <textarea name="expected_output"></textarea>
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
        <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Save Test Type</button>
        <a class="btn btn-soft" href="<?= url('admin/testing-types/index.php') ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</form>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
