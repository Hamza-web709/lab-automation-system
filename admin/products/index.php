<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$pageTitle = 'Products';
$activePage = 'products';

$q = trim($_GET['q'] ?? '');
$productTypeId = (int) ($_GET['product_type_id'] ?? 0);
$status = $_GET['status'] ?? '';
$where = [];
$params = [];

if ($q !== '') {
    $searchTerm = '%' . $q . '%';
    $where[] = '(p.product_id LIKE :q_product_id
        OR p.product_name LIKE :q_product_name
        OR p.product_code LIKE :q_product_code
        OR p.batch_no LIKE :q_batch_no)';
    $params['q_product_id'] = $searchTerm;
    $params['q_product_name'] = $searchTerm;
    $params['q_product_code'] = $searchTerm;
    $params['q_batch_no'] = $searchTerm;
}
if ($productTypeId > 0) {
    $where[] = 'p.product_type_id = :product_type_id';
    $params['product_type_id'] = $productTypeId;
}
if (in_array($status, ['manufactured', 'under_testing', 'passed_internal', 'failed_internal', 'sent_to_cpri', 'approved', 'sent_for_remaking'], true)) {
    $where[] = 'p.current_status = :status';
    $params['status'] = $status;
}

$sql = 'SELECT p.*, pt.name AS product_type_name
        FROM products p
        INNER JOIN product_types pt ON pt.id = p.product_type_id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY p.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$productTypes = $pdo->query('SELECT id, name FROM product_types ORDER BY name')->fetchAll();

include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Manufacturing intake</p>
        <h2>Products</h2>
        <p>Register and track manufactured items as they move through testing.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('admin/products/create.php') ?>"><i data-lucide="plus"></i> Add Product</a>
</div>

<form class="filter-bar surface-card" method="get" data-animate="fade-up">
    <div class="field">
        <label>Search</label>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="ID, name, code, batch">
    </div>
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
    <a class="btn btn-soft" href="<?= url('admin/products/index.php') ?>"><i data-lucide="rotate-ccw"></i> Reset</a>
</form>

<section class="surface-card table-shell" data-animate="fade-up">
    <?php if ($products): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Batch</th>
                    <th>Manufacturing Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr data-search-row>
                        <td class="record-title">
                            <strong><?= e($product['product_name']) ?></strong>
                            <span><?= e($product['product_id']) ?> / <?= e($product['product_code']) ?></span>
                        </td>
                        <td><?= e($product['product_type_name']) ?></td>
                        <td><?= e($product['batch_no'] ?: '-') ?></td>
                        <td><?= e(formatDate($product['manufacturing_date'])) ?></td>
                        <td><?= statusBadge($product['current_status']) ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="icon-btn" href="<?= url('admin/products/view.php?id=' . (int) $product['id']) ?>" aria-label="View"><i data-lucide="eye"></i></a>
                                <a class="icon-btn" href="<?= url('admin/products/edit.php?id=' . (int) $product['id']) ?>" aria-label="Edit"><i data-lucide="pencil"></i></a>
                                <form method="post" action="<?= url('admin/products/delete.php') ?>" onsubmit="return confirm('Delete this product and its test records?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                    <button class="icon-btn" type="submit" aria-label="Delete"><i data-lucide="trash-2"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-lottie" data-lottie="<?= asset('lottie/empty.json') ?>"></div>
            <h3>No products found</h3>
            <p>Add a manufactured product to begin the testing workflow.</p>
            <a class="btn btn-primary" href="<?= url('admin/products/create.php') ?>"><i data-lucide="plus"></i> Add Product</a>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
