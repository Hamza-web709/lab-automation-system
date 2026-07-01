<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT p.*, pt.name AS product_type_name, pt.code AS product_type_code
     FROM products p
     INNER JOIN product_types pt ON pt.id = p.product_type_id
     WHERE p.id = :id'
);
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();
if (!$product) {
    set_flash('error', 'Product not found.');
    redirect('admin/products/index.php');
}

$testStmt = $pdo->prepare(
    'SELECT tr.*, tt.name AS testing_type, td.name AS department
     FROM testing_records tr
     INNER JOIN testing_types tt ON tt.id = tr.testing_type_id
     INNER JOIN testing_departments td ON td.id = tr.department_id
     WHERE tr.product_id_ref = :id
     ORDER BY tr.test_date DESC, tr.created_at DESC'
);
$testStmt->execute(['id' => $id]);
$tests = $testStmt->fetchAll();

$pageTitle = 'Product Details';
$activePage = 'products';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Product trace</p>
        <h2><?= e($product['product_name']) ?></h2>
        <p><?= e($product['product_id']) ?> · <?= e($product['product_type_name']) ?> · <?= statusBadge($product['current_status']) ?></p>
    </div>
    <div class="form-actions">
        <a class="btn btn-primary" href="<?= url('admin/tests/create.php?product_id=' . (int) $product['id']) ?>"><i data-lucide="clipboard-plus"></i> Add Test</a>
        <a class="btn btn-soft" href="<?= url('admin/products/edit.php?id=' . (int) $product['id']) ?>"><i data-lucide="pencil"></i> Edit</a>
    </div>
</div>

<section class="report-paper surface-card" data-animate="fade-up">
    <div class="report-grid">
        <?php
        $fields = [
            'Product ID' => $product['product_id'],
            'Product Type' => $product['product_type_name'] . ' (' . $product['product_type_code'] . ')',
            'Product Code' => $product['product_code'],
            'Revise No' => $product['revise_no'],
            'Manufacturing No' => $product['manufacturing_no'],
            'Batch No' => $product['batch_no'] ?: '-',
            'Manufacturing Date' => formatDate($product['manufacturing_date']),
            'Current Status' => humanize($product['current_status']),
        ];
        foreach ($fields as $label => $value):
        ?>
            <div class="report-field">
                <span><?= e($label) ?></span>
                <strong><?= e($value) ?></strong>
            </div>
        <?php endforeach; ?>
        <div class="report-field" style="grid-column: 1 / -1;">
            <span>Description</span>
            <p><?= e($product['description'] ?: '-') ?></p>
        </div>
    </div>
</section>

<section class="panel surface-card" data-animate="fade-up" style="margin-top: 18px;">
    <div class="panel-header">
        <h3>Testing History</h3>
    </div>
    <?php if ($tests): ?>
        <div class="table-shell">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Test ID</th>
                        <th>Testing Type</th>
                        <th>Department</th>
                        <th>Result</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests as $test): ?>
                        <tr>
                            <td><?= e($test['test_id']) ?></td>
                            <td><?= e($test['testing_type']) ?></td>
                            <td><?= e($test['department']) ?></td>
                            <td><?= resultBadge($test['result']) ?></td>
                            <td><?= statusBadge($test['status']) ?></td>
                            <td><?= e(formatDate($test['test_date'])) ?></td>
                            <td><a class="icon-btn" href="<?= url('admin/tests/view.php?id=' . (int) $test['id']) ?>" aria-label="View test"><i data-lucide="eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-lottie" data-lottie="<?= asset('lottie/empty.json') ?>"></div>
            <h3>No tests attached</h3>
            <p>Create the first testing record for this product.</p>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
