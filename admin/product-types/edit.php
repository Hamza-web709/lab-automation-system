<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin', 'lab_manager']);
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM product_types WHERE id = :id');
$stmt->execute(['id' => $id]);
$type = $stmt->fetch();
if (!$type) {
    set_flash('error', 'Product type not found.');
    redirect('admin/product-types/index.php');
}
$pageTitle = 'Edit Product Type';
$activePage = 'product-types';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Master data</p>
        <h2>Edit Product Type</h2>
        <p>Changes affect future product registration and test categorization.</p>
    </div>
</div>
<form class="form-card surface-card" method="post" action="<?= url('admin/product-types/update.php') ?>" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $type['id'] ?>">
    <div class="form-grid">
        <div class="field">
            <label>Name <span class="required">*</span></label>
            <input type="text" name="name" value="<?= e($type['name']) ?>" required maxlength="120">
            <small class="field-error">Name is required.</small>
        </div>
        <div class="field">
            <label>Code <span class="required">*</span></label>
            <input type="text" name="code" value="<?= e($type['code']) ?>" required maxlength="10">
            <small class="field-error">Code is required.</small>
        </div>
        <div class="field span-2">
            <label>Description</label>
            <textarea name="description"><?= e($type['description']) ?></textarea>
        </div>
        <div class="field">
            <label>Status</label>
            <select name="status">
                <option value="active" <?= optionSelected($type['status'], 'active') ?>>Active</option>
                <option value="inactive" <?= optionSelected($type['status'], 'inactive') ?>>Inactive</option>
            </select>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Update Type</button>
        <a class="btn btn-soft" href="<?= url('admin/product-types/index.php') ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</form>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
