<?php
require_once __DIR__ . '/../includes/role_check.php';
requireRole('lab_manager');
$pdo = getPDO();
$pageTitle = 'Products';
$activePage = 'products';
$panelName = 'Lab Manager Panel';
$panelBrand = 'LabFlow';
$products = $pdo->query(
    'SELECT p.*, pt.name AS product_type_name
     FROM products p
     INNER JOIN product_types pt ON pt.id = p.product_type_id
     ORDER BY p.created_at DESC'
)->fetchAll();
include __DIR__ . '/../includes/panel_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div><p class="eyebrow">Products</p><h2>Product Testing Queue</h2><p>View products and create testing records without system-setting access.</p></div>
    <a class="btn btn-primary" href="<?= url('lab-manager/create-test.php') ?>"><i data-lucide="clipboard-plus"></i> Create Test</a>
</div>
<section class="surface-card table-shell" data-animate="fade-up">
    <table class="data-table">
        <thead><tr><th>Product</th><th>Type</th><th>Batch</th><th>Status</th><th>Manufacturing Date</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td class="record-title"><strong><?= e($product['product_id']) ?></strong><span><?= e($product['product_name']) ?></span></td>
                    <td><?= e($product['product_type_name']) ?></td>
                    <td><?= e($product['batch_no'] ?: '-') ?></td>
                    <td><?= statusBadge($product['current_status']) ?></td>
                    <td><?= e(formatDate($product['manufacturing_date'])) ?></td>
                    <td><a class="btn btn-soft" href="<?= url('lab-manager/create-test.php?product_id=' . (int) $product['id']) ?>"><i data-lucide="plus"></i> Create Test</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../includes/panel_end.php'; ?>
