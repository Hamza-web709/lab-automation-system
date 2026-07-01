<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$pageTitle = 'Testing Types';
$activePage = 'testing-types';

$q = trim($_GET['q'] ?? '');
$departmentId = (int) ($_GET['department_id'] ?? 0);
$productTypeId = (int) ($_GET['product_type_id'] ?? 0);
$status = $_GET['status'] ?? '';
$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(tt.name LIKE :q OR tt.testing_code LIKE :q)';
    $params['q'] = '%' . $q . '%';
}
if ($departmentId > 0) {
    $where[] = 'tt.department_id = :department_id';
    $params['department_id'] = $departmentId;
}
if ($productTypeId > 0) {
    $where[] = 'tt.product_type_id = :product_type_id';
    $params['product_type_id'] = $productTypeId;
}
if (in_array($status, ['active', 'inactive'], true)) {
    $where[] = 'tt.status = :status';
    $params['status'] = $status;
}

$sql = 'SELECT tt.*, td.name AS department_name, pt.name AS product_type_name
        FROM testing_types tt
        INNER JOIN testing_departments td ON td.id = tt.department_id
        INNER JOIN product_types pt ON pt.id = tt.product_type_id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY tt.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$departments = $pdo->query('SELECT id, name FROM testing_departments ORDER BY name')->fetchAll();
$productTypes = $pdo->query('SELECT id, name FROM product_types ORDER BY name')->fetchAll();

include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Testing setup</p>
        <h2>Testing Types</h2>
        <p>Define test modules, expected outputs, and acceptance criteria.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('admin/testing-types/create.php') ?>"><i data-lucide="plus"></i> Add Test Type</a>
</div>

<form class="filter-bar surface-card" method="get" data-animate="fade-up">
    <div class="field">
        <label>Search</label>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Name or code">
    </div>
    <div class="field">
        <label>Department</label>
        <select name="department_id">
            <option value="">All departments</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?= (int) $department['id'] ?>" <?= optionSelected($departmentId, $department['id']) ?>><?= e($department['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label>Product Type</label>
        <select name="product_type_id">
            <option value="">All product types</option>
            <?php foreach ($productTypes as $type): ?>
                <option value="<?= (int) $type['id'] ?>" <?= optionSelected($productTypeId, $type['id']) ?>><?= e($type['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label>Status</label>
        <select name="status">
            <option value="">All statuses</option>
            <option value="active" <?= optionSelected($status, 'active') ?>>Active</option>
            <option value="inactive" <?= optionSelected($status, 'inactive') ?>>Inactive</option>
        </select>
    </div>
    <button class="btn btn-secondary" type="submit"><i data-lucide="filter"></i> Filter</button>
</form>

<section class="surface-card table-shell" data-animate="fade-up">
    <?php if ($rows): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Testing Type</th>
                    <th>Code</th>
                    <th>Department</th>
                    <th>Product Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr data-search-row>
                        <td><strong><?= e($row['name']) ?></strong><br><span><?= e($row['description']) ?></span></td>
                        <td><?= e($row['testing_code']) ?></td>
                        <td><?= e($row['department_name']) ?></td>
                        <td><?= e($row['product_type_name']) ?></td>
                        <td><?= statusBadge($row['status']) ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="icon-btn" href="<?= url('admin/testing-types/edit.php?id=' . (int) $row['id']) ?>" aria-label="Edit"><i data-lucide="pencil"></i></a>
                                <form method="post" action="<?= url('admin/testing-types/delete.php') ?>" onsubmit="return confirm('Delete this testing type?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
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
            <h3>No testing types found</h3>
            <p>Create testing modules with criteria and expected outputs before adding test records.</p>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
