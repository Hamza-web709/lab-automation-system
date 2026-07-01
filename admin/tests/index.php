<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$pageTitle = 'Testing Records';
$activePage = 'tests';

$q = trim($_GET['q'] ?? '');
$result = $_GET['result'] ?? '';
$status = $_GET['status'] ?? '';
$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(tr.test_id LIKE :q OR p.product_id LIKE :q OR p.product_name LIKE :q OR tt.name LIKE :q)';
    $params['q'] = '%' . $q . '%';
}
if (in_array($result, ['pass', 'fail', 'pending'], true)) {
    $where[] = 'tr.result = :result';
    $params['result'] = $result;
}
if (in_array($status, ['pending', 'in_progress', 'completed', 'sent_to_next_department', 'sent_to_cpri', 'sent_for_remaking'], true)) {
    $where[] = 'tr.status = :status';
    $params['status'] = $status;
}

$sql = 'SELECT tr.*, p.product_id, p.product_name, tt.name AS testing_type, td.name AS department
        FROM testing_records tr
        INNER JOIN products p ON p.id = tr.product_id_ref
        INNER JOIN testing_types tt ON tt.id = tr.testing_type_id
        INNER JOIN testing_departments td ON td.id = tr.department_id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY tr.test_date DESC, tr.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Testing workflow</p>
        <h2>Testing Records</h2>
        <p>Create, review, and print detailed product testing outcomes.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('admin/tests/create.php') ?>"><i data-lucide="plus"></i> Add Test Record</a>
</div>

<form class="filter-bar surface-card" method="get" data-animate="fade-up">
    <div class="field">
        <label>Search</label>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Test ID, Product ID, Name">
    </div>
    <div class="field">
        <label>Result</label>
        <select name="result">
            <option value="">All results</option>
            <option value="pass" <?= optionSelected($result, 'pass') ?>>Pass</option>
            <option value="fail" <?= optionSelected($result, 'fail') ?>>Fail</option>
            <option value="pending" <?= optionSelected($result, 'pending') ?>>Pending</option>
        </select>
    </div>
    <div class="field">
        <label>Status</label>
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach (['pending', 'in_progress', 'completed', 'sent_to_next_department', 'sent_to_cpri', 'sent_for_remaking'] as $item): ?>
                <option value="<?= e($item) ?>" <?= optionSelected($status, $item) ?>><?= e(humanize($item)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-secondary" type="submit"><i data-lucide="filter"></i> Filter</button>
    <a class="btn btn-soft" href="<?= url('admin/tests/index.php') ?>"><i data-lucide="rotate-ccw"></i> Reset</a>
</form>

<section class="surface-card table-shell" data-animate="fade-up">
    <?php if ($records): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Product</th>
                    <th>Department</th>
                    <th>Result</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr data-search-row>
                        <td class="record-title"><strong><?= e($record['test_id']) ?></strong><span><?= e($record['testing_type']) ?></span></td>
                        <td class="record-title"><strong><?= e($record['product_id']) ?></strong><span><?= e($record['product_name']) ?></span></td>
                        <td><?= e($record['department']) ?></td>
                        <td><?= resultBadge($record['result']) ?></td>
                        <td><?= statusBadge($record['status']) ?></td>
                        <td><?= e(formatDate($record['test_date'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="icon-btn" href="<?= url('admin/tests/view.php?id=' . (int) $record['id']) ?>" aria-label="View"><i data-lucide="eye"></i></a>
                                <a class="icon-btn" href="<?= url('admin/tests/report.php?id=' . (int) $record['id']) ?>" aria-label="Report"><i data-lucide="file-text"></i></a>
                                <a class="icon-btn" href="<?= url('admin/tests/edit.php?id=' . (int) $record['id']) ?>" aria-label="Edit"><i data-lucide="pencil"></i></a>
                                <form method="post" action="<?= url('admin/tests/delete.php') ?>" onsubmit="return confirm('Delete this testing record?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
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
            <h3>No testing records found</h3>
            <p>Add a testing record after product registration.</p>
            <a class="btn btn-primary" href="<?= url('admin/tests/create.php') ?>"><i data-lucide="plus"></i> Add Test Record</a>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
