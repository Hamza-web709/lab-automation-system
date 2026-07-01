<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin', 'lab_manager']);
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM testing_types WHERE id = :id');
$stmt->execute(['id' => $id]);
$testingType = $stmt->fetch();
if (!$testingType) {
    set_flash('error', 'Testing type not found.');
    redirect('admin/testing-types/index.php');
}
$departments = $pdo->query('SELECT id, name FROM testing_departments ORDER BY name')->fetchAll();
$productTypes = $pdo->query('SELECT id, name FROM product_types ORDER BY name')->fetchAll();
$pageTitle = 'Edit Testing Type';
$activePage = 'testing-types';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Testing setup</p>
        <h2>Edit Testing Type</h2>
        <p>Update criteria and expected outputs for future records.</p>
    </div>
</div>
<form class="form-card surface-card" method="post" action="<?= url('admin/testing-types/update.php') ?>" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $testingType['id'] ?>">
    <div class="form-grid">
        <div class="field">
            <label>Department <span class="required">*</span></label>
            <select name="department_id" required>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= (int) $department['id'] ?>" <?= optionSelected($testingType['department_id'], $department['id']) ?>><?= e($department['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Product Type <span class="required">*</span></label>
            <select name="product_type_id" required>
                <?php foreach ($productTypes as $type): ?>
                    <option value="<?= (int) $type['id'] ?>" <?= optionSelected($testingType['product_type_id'], $type['id']) ?>><?= e($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Name <span class="required">*</span></label>
            <input type="text" name="name" value="<?= e($testingType['name']) ?>" required maxlength="160">
        </div>
        <div class="field">
            <label>Testing Code <span class="required">*</span></label>
            <input type="text" name="testing_code" value="<?= e($testingType['testing_code']) ?>" required maxlength="10">
        </div>
        <div class="field span-2">
            <label>Description</label>
            <textarea name="description"><?= e($testingType['description']) ?></textarea>
        </div>
        <div class="field span-2">
            <label>Criteria</label>
            <textarea name="criteria"><?= e($testingType['criteria']) ?></textarea>
        </div>
        <div class="field span-2">
            <label>Expected Output</label>
            <textarea name="expected_output"><?= e($testingType['expected_output']) ?></textarea>
        </div>
        <div class="field">
            <label>Status</label>
            <select name="status">
                <option value="active" <?= optionSelected($testingType['status'], 'active') ?>>Active</option>
                <option value="inactive" <?= optionSelected($testingType['status'], 'inactive') ?>>Inactive</option>
            </select>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Update Test Type</button>
        <a class="btn btn-soft" href="<?= url('admin/testing-types/index.php') ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</form>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
