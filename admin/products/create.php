<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$productTypes = $pdo->query('SELECT id, name, code FROM product_types WHERE status = "active" ORDER BY name')->fetchAll();
$pageTitle = 'Add Product';
$activePage = 'products';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Manufacturing intake</p>
        <h2>Add Product</h2>
        <p>The product ID is generated from product type code, revision, and manufacturing number.</p>
    </div>
</div>
<form class="form-card surface-card" method="post" action="<?= url('admin/products/store.php') ?>" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="field">
            <label>Product Type <span class="required">*</span></label>
            <select name="product_type_id" required>
                <option value="">Select product type</option>
                <?php foreach ($productTypes as $type): ?>
                    <option value="<?= (int) $type['id'] ?>"><?= e($type['name']) ?> (<?= e($type['code']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <small class="field-error">Product type is required.</small>
        </div>
        <div class="field">
            <label>Product Name <span class="required">*</span></label>
            <input type="text" name="product_name" required maxlength="160">
            <small class="field-error">Product name is required.</small>
        </div>
        <div class="field">
            <label>Product Code <span class="required">*</span></label>
            <input type="text" name="product_code" required maxlength="10" placeholder="SGR">
            <small class="field-error">Product code is required.</small>
        </div>
        <div class="field">
            <label>Revise No <span class="required">*</span></label>
            <input type="text" name="revise_no" required maxlength="20" placeholder="R1">
            <small class="field-error">Revision is required.</small>
        </div>
        <div class="field">
            <label>Manufacturing No <span class="required">*</span></label>
            <input type="text" name="manufacturing_no" required maxlength="40" placeholder="10001">
            <small class="field-error">Manufacturing number is required.</small>
        </div>
        <div class="field">
            <label>Batch No</label>
            <input type="text" name="batch_no" maxlength="80">
        </div>
        <div class="field">
            <label>Manufacturing Date</label>
            <input type="date" name="manufacturing_date">
        </div>
        <div class="field span-2">
            <label>Description</label>
            <textarea name="description"></textarea>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Save Product</button>
        <a class="btn btn-soft" href="<?= url('admin/products/index.php') ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</form>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
