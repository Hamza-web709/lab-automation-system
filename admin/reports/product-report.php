<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$pageTitle = 'Product Report';
$activePage = 'reports';
$status = $_GET['status'] ?? '';
$productTypeId = (int) ($_GET['product_type_id'] ?? 0);
$where = [];
$params = [];

if ($productTypeId > 0) {
    $where[] = 'p.product_type_id = :product_type_id';
    $params['product_type_id'] = $productTypeId;
}
if (in_array($status, ['manufactured', 'under_testing', 'passed_internal', 'failed_internal', 'sent_to_cpri', 'approved', 'sent_for_remaking'], true)) {
    $where[] = 'p.current_status = :status';
    $params['status'] = $status;
}

$sql = 'SELECT p.*, pt.name AS product_type_name,
               COUNT(tr.id) AS total_tests,
               SUM(tr.result = "pass") AS pass_count,
               SUM(tr.result = "fail") AS fail_count
        FROM products p
        INNER JOIN product_types pt ON pt.id = p.product_type_id
        LEFT JOIN testing_records tr ON tr.product_id_ref = p.id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' GROUP BY p.id ORDER BY p.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$productTypes = $pdo->query('SELECT id, name FROM product_types ORDER BY name')->fetchAll();

include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Report</p>
        <h2>Product Report</h2>
        <p>Manufacturing and current workflow status across products.</p>
    </div>
    <button class="btn btn-primary" type="button" onclick="window.print()"><i data-lucide="printer"></i> Print</button>
</div>

<form class="filter-bar surface-card" method="get" data-animate="fade-up">
    <div class="field">
        <label>Product Type</label>
        <select name="product_type_id">
            <option value="">All types</option>
            <?php foreach ($productTypes as $type): ?>
                <option value="<?= (int) $type['id'] ?>" <?= optionSelected($productTypeId, $type['id']) ?>><?= e($type['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label>Status</label>
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach (['manufactured', 'under_testing', 'passed_internal', 'failed_internal', 'sent_to_cpri', 'approved', 'sent_for_remaking'] as $item): ?>
                <option value="<?= e($item) ?>" <?= optionSelected($status, $item) ?>><?= e(humanize($item)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-secondary" type="submit"><i data-lucide="filter"></i> Filter</button>
</form>

<section class="surface-card table-shell" data-animate="fade-up">
    <table class="data-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Type</th>
                <th>Status</th>
                <th>Tests</th>
                <th>Pass</th>
                <th>Fail</th>
                <th>Manufacturing Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td class="record-title"><strong><?= e($product['product_id']) ?></strong><span><?= e($product['product_name']) ?></span></td>
                    <td><?= e($product['product_type_name']) ?></td>
                    <td><?= statusBadge($product['current_status']) ?></td>
                    <td><?= (int) $product['total_tests'] ?></td>
                    <td><?= (int) $product['pass_count'] ?></td>
                    <td><?= (int) $product['fail_count'] ?></td>
                    <td><?= e(formatDate($product['manufacturing_date'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
