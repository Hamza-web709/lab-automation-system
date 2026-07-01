<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$pageTitle = 'Testing Departments';
$activePage = 'testing-departments';

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(name LIKE :q OR code LIKE :q)';
    $params['q'] = '%' . $q . '%';
}
if (in_array($status, ['active', 'inactive'], true)) {
    $where[] = 'status = :status';
    $params['status'] = $status;
}

$sql = 'SELECT * FROM testing_departments';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Lab structure</p>
        <h2>Testing Departments</h2>
        <p>Manage the departments that own each testing stage.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('admin/testing-departments/create.php') ?>"><i data-lucide="plus"></i> Add Department</a>
</div>

<form class="filter-bar surface-card" method="get" data-animate="fade-up">
    <div class="field">
        <label>Search</label>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Name or code">
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
    <a class="btn btn-soft" href="<?= url('admin/testing-departments/index.php') ?>"><i data-lucide="rotate-ccw"></i> Reset</a>
</form>

<section class="surface-card table-shell" data-animate="fade-up">
    <?php if ($rows): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr data-search-row>
                        <td><strong><?= e($row['name']) ?></strong><br><span><?= e($row['description']) ?></span></td>
                        <td><?= e($row['code']) ?></td>
                        <td><?= statusBadge($row['status']) ?></td>
                        <td><?= e(formatDate($row['created_at'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="icon-btn" href="<?= url('admin/testing-departments/edit.php?id=' . (int) $row['id']) ?>" aria-label="Edit"><i data-lucide="pencil"></i></a>
                                <form method="post" action="<?= url('admin/testing-departments/delete.php') ?>" onsubmit="return confirm('Delete this department?');">
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
            <h3>No departments found</h3>
            <p>Add a testing department to organize test definitions.</p>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
