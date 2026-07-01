<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$pageTitle = 'Test Report';
$activePage = 'reports';
$result = $_GET['result'] ?? '';
$where = [];
$params = [];
if (in_array($result, ['pass', 'fail', 'pending'], true)) {
    $where[] = 'tr.result = :result';
    $params['result'] = $result;
}
$sql = 'SELECT tr.*, p.product_id, p.product_name, tt.name AS testing_type_name, td.name AS department_name
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
        <p class="eyebrow">Report</p>
        <h2>Testing Records Report</h2>
        <p>Review test results and open printable single-record reports.</p>
    </div>
    <button class="btn btn-primary" type="button" onclick="window.print()"><i data-lucide="printer"></i> Print List</button>
</div>

<form class="filter-bar surface-card" method="get" data-animate="fade-up">
    <div class="field">
        <label>Result</label>
        <select name="result">
            <option value="">All results</option>
            <?php foreach (['pass', 'fail', 'pending'] as $item): ?>
                <option value="<?= e($item) ?>" <?= optionSelected($result, $item) ?>><?= e(humanize($item)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-secondary" type="submit"><i data-lucide="filter"></i> Filter</button>
</form>

<section class="surface-card table-shell" data-animate="fade-up">
    <table class="data-table">
        <thead>
            <tr>
                <th>Test</th>
                <th>Product</th>
                <th>Testing Type</th>
                <th>Department</th>
                <th>Result</th>
                <th>Status</th>
                <th>Date</th>
                <th>Print</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><?= e($record['test_id']) ?></td>
                    <td class="record-title"><strong><?= e($record['product_id']) ?></strong><span><?= e($record['product_name']) ?></span></td>
                    <td><?= e($record['testing_type_name']) ?></td>
                    <td><?= e($record['department_name']) ?></td>
                    <td><?= resultBadge($record['result']) ?></td>
                    <td><?= statusBadge($record['status']) ?></td>
                    <td><?= e(formatDate($record['test_date'])) ?></td>
                    <td><a class="icon-btn" href="<?= url('admin/reports/print-report.php?id=' . (int) $record['id']) ?>" aria-label="Print"><i data-lucide="printer"></i></a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
