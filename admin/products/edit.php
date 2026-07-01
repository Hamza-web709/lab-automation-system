<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();
if (!$product) {
    set_flash('error', 'Product not found.');
    redirect('admin/products/index.php');
}
$productTypes = $pdo->query('SELECT id, name, code FROM product_types ORDER BY name')->fetchAll();
$statuses = ['manufactured', 'under_testing', 'passed_internal', 'failed_internal', 'sent_to_cpri', 'approved', 'sent_for_remaking'];
$pageTitle = 'Edit Product';
$activePage = 'products';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Manufacturing intake</p>
        <h2>Edit Product</h2>
        <p>Product ID <?= e($product['product_id']) ?> remains fixed for traceability.</p>
    </div>
</div>
<form class="form-card surface-card" method="post" action="<?= url('admin/products/update.php') ?>" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
    <div class="form-grid">
        <div class="field">
            <label>Product ID</label>
            <input type="text" value="<?= e($product['product_id']) ?>" disabled>
        </div>
        <div class="field">
            <label>Product Type <span class="required">*</span></label>
            <select name="product_type_id" required>
                <?php foreach ($productTypes as $type): ?>
                    <option value="<?= (int) $type['id'] ?>" <?= optionSelected($product['product_type_id'], $type['id']) ?>><?= e($type['name']) ?> (<?= e($type['code']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Product Name <span class="required">*</span></label>
            <input type="text" name="product_name" value="<?= e($product['product_name']) ?>" required maxlength="160">
        </div>
        <div class="field">
            <label>Product Code <span class="required">*</span></label>
            <input type="text" name="product_code" value="<?= e($product['product_code']) ?>" required maxlength="10">
        </div>
        <div class="field">
            <label>Revise No <span class="required">*</span></label>
            <input type="text" name="revise_no" value="<?= e($product['revise_no']) ?>" required maxlength="20">
        </div>
        <div class="field">
            <label>Manufacturing No <span class="required">*</span></label>
            <input type="text" name="manufacturing_no" value="<?= e($product['manufacturing_no']) ?>" required maxlength="40">
        </div>
        <div class="field">
            <label>Batch No</label>
            <input type="text" name="batch_no" value="<?= e($product['batch_no']) ?>" maxlength="80">
        </div>
        <div class="field">
            <label>Manufacturing Date</label>
            <input type="date" name="manufacturing_date" value="<?= e($product['manufacturing_date']) ?>">
        </div>
        <div class="field">
            <label>Status</label>
            <select name="current_status">
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= e($status) ?>" <?= optionSelected($product['current_status'], $status) ?>><?= e(humanize($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field span-2">
            <label>Description</label>
            <textarea name="description"><?= e($product['description']) ?></textarea>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Update Product</button>
        <a class="btn btn-soft" href="<?= url('admin/products/index.php') ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</form>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
