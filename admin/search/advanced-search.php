<?php
require_once __DIR__ . '/../../includes/role_check.php';
requireAnyRole(['admin', 'lab_manager', 'tester']);
$pdo = getPDO();
$pageTitle = 'Advanced Search';
$activePage = 'advanced-search';
$panelName = match (current_user()['role'] ?? '') {
    'lab_manager' => 'Lab Manager Panel',
    'tester' => 'Tester Panel',
    default => 'Admin Panel',
};
$panelBrand = 'LabFlow';

$filters = [
    'product_id' => trim($_GET['product_id'] ?? ''),
    'test_id' => trim($_GET['test_id'] ?? ''),
    'product_type_id' => (int) ($_GET['product_type_id'] ?? 0),
    'product_name' => trim($_GET['product_name'] ?? ''),
    'department_id' => (int) ($_GET['department_id'] ?? 0),
    'testing_type_id' => (int) ($_GET['testing_type_id'] ?? 0),
    'result' => $_GET['result'] ?? '',
    'status' => $_GET['status'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'tester_name' => trim($_GET['tester_name'] ?? ''),
    'next_action' => $_GET['next_action'] ?? '',
];

$where = [];
$params = [];

// Advanced search builds one parameterized WHERE clause from optional filters.
if ($filters['product_id'] !== '') {
    $where[] = 'p.product_id LIKE :product_id';
    $params['product_id'] = '%' . $filters['product_id'] . '%';
}
if ($filters['test_id'] !== '') {
    $where[] = 'tr.test_id LIKE :test_id';
    $params['test_id'] = '%' . $filters['test_id'] . '%';
}
if ($filters['product_type_id'] > 0) {
    $where[] = 'p.product_type_id = :product_type_id';
    $params['product_type_id'] = $filters['product_type_id'];
}
if ($filters['product_name'] !== '') {
    $where[] = 'p.product_name LIKE :product_name';
    $params['product_name'] = '%' . $filters['product_name'] . '%';
}
if ($filters['department_id'] > 0) {
    $where[] = 'tr.department_id = :department_id';
    $params['department_id'] = $filters['department_id'];
}
if ($filters['testing_type_id'] > 0) {
    $where[] = 'tr.testing_type_id = :testing_type_id';
    $params['testing_type_id'] = $filters['testing_type_id'];
}
if (in_array($filters['result'], ['pass', 'fail', 'pending'], true)) {
    $where[] = 'tr.result = :result';
    $params['result'] = $filters['result'];
}
if (in_array($filters['status'], ['pending', 'in_progress', 'completed', 'sent_to_next_department', 'sent_to_cpri', 'sent_for_remaking'], true)) {
    $where[] = 'tr.status = :status';
    $params['status'] = $filters['status'];
}
if ($filters['date_from'] !== '') {
    $where[] = 'tr.test_date >= :date_from';
    $params['date_from'] = $filters['date_from'];
}
if ($filters['date_to'] !== '') {
    $where[] = 'tr.test_date <= :date_to';
    $params['date_to'] = $filters['date_to'];
}
if ($filters['tester_name'] !== '') {
    $where[] = 'tp.person_name LIKE :tester_name';
    $params['tester_name'] = '%' . $filters['tester_name'] . '%';
}
if (in_array($filters['next_action'], ['none', 'next_test', 'send_to_cpri', 'send_for_remaking'], true)) {
    $where[] = 'tr.next_action = :next_action';
    $params['next_action'] = $filters['next_action'];
}

// Testers can search only their own assigned records.
if (isTester()) {
    $where[] = 'tr.assigned_tester_id = :current_tester_id';
    $params['current_tester_id'] = current_user_id();
}

$sql = 'SELECT tr.*, p.product_id, p.product_name, pt.name AS product_type_name,
               tt.name AS testing_type_name, td.name AS department_name,
               GROUP_CONCAT(DISTINCT tp.person_name ORDER BY tp.person_name SEPARATOR ", ") AS tester_names
        FROM testing_records tr
        INNER JOIN products p ON p.id = tr.product_id_ref
        INNER JOIN product_types pt ON pt.id = p.product_type_id
        INNER JOIN testing_types tt ON tt.id = tr.testing_type_id
        INNER JOIN testing_departments td ON td.id = tr.department_id
        LEFT JOIN test_persons tp ON tp.testing_record_id = tr.id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' GROUP BY tr.id ORDER BY tr.test_date DESC, tr.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

$productTypes = $pdo->query('SELECT id, name FROM product_types ORDER BY name')->fetchAll();
$departments = $pdo->query('SELECT id, name FROM testing_departments ORDER BY name')->fetchAll();
$testingTypes = $pdo->query('SELECT id, name FROM testing_types ORDER BY name')->fetchAll();

include __DIR__ . '/../../includes/panel_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Record discovery</p>
        <h2>Advanced Search</h2>
        <p>Filter testing history by product, test, department, result, date, tester, and next action.</p>
    </div>
</div>

<form class="form-card surface-card" method="get" data-animate="fade-up">
    <div class="form-grid">
        <div class="field">
            <label>Product ID</label>
            <input type="search" name="product_id" value="<?= e($filters['product_id']) ?>">
        </div>
        <div class="field">
            <label>Test ID</label>
            <input type="search" name="test_id" value="<?= e($filters['test_id']) ?>">
        </div>
        <div class="field">
            <label>Product Type</label>
            <select name="product_type_id">
                <option value="">All product types</option>
                <?php foreach ($productTypes as $type): ?>
                    <option value="<?= (int) $type['id'] ?>" <?= optionSelected($filters['product_type_id'], $type['id']) ?>><?= e($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Product Name</label>
            <input type="search" name="product_name" value="<?= e($filters['product_name']) ?>">
        </div>
        <div class="field">
            <label>Testing Department</label>
            <select name="department_id">
                <option value="">All departments</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= (int) $department['id'] ?>" <?= optionSelected($filters['department_id'], $department['id']) ?>><?= e($department['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Testing Type</label>
            <select name="testing_type_id">
                <option value="">All testing types</option>
                <?php foreach ($testingTypes as $type): ?>
                    <option value="<?= (int) $type['id'] ?>" <?= optionSelected($filters['testing_type_id'], $type['id']) ?>><?= e($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Result</label>
            <select name="result">
                <option value="">All results</option>
                <?php foreach (['pass', 'fail', 'pending'] as $item): ?>
                    <option value="<?= e($item) ?>" <?= optionSelected($filters['result'], $item) ?>><?= e(humanize($item)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Status</label>
            <select name="status">
                <option value="">All statuses</option>
                <?php foreach (['pending', 'in_progress', 'completed', 'sent_to_next_department', 'sent_to_cpri', 'sent_for_remaking'] as $item): ?>
                    <option value="<?= e($item) ?>" <?= optionSelected($filters['status'], $item) ?>><?= e(humanize($item)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Date From</label>
            <input type="date" name="date_from" value="<?= e($filters['date_from']) ?>">
        </div>
        <div class="field">
            <label>Date To</label>
            <input type="date" name="date_to" value="<?= e($filters['date_to']) ?>">
        </div>
        <div class="field">
            <label>Tester Person</label>
            <input type="search" name="tester_name" value="<?= e($filters['tester_name']) ?>">
        </div>
        <div class="field">
            <label>Next Action</label>
            <select name="next_action">
                <option value="">All actions</option>
                <?php foreach (['none', 'next_test', 'send_to_cpri', 'send_for_remaking'] as $item): ?>
                    <option value="<?= e($item) ?>" <?= optionSelected($filters['next_action'], $item) ?>><?= e(humanize($item)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit"><i data-lucide="search"></i> Search</button>
        <a class="btn btn-soft" href="<?= url('admin/search/advanced-search.php') ?>"><i data-lucide="rotate-ccw"></i> Reset</a>
    </div>
</form>

<section class="panel surface-card" data-animate="fade-up" style="margin-top: 18px;">
    <div class="panel-header">
        <h3>Search Results</h3>
        <span class="badge badge-primary"><?= count($records) ?> found</span>
    </div>
    <?php if ($records): ?>
        <div class="table-shell">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Test ID</th>
                        <th>Product</th>
                        <th>Testing Type</th>
                        <th>Department</th>
                        <th>Result</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
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
                            <td>
                                <div class="table-actions">
                                    <a class="icon-btn" href="<?= url('admin/reports/print-report.php?id=' . (int) $record['id']) ?>" aria-label="Print"><i data-lucide="printer"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-lottie" data-lottie="<?= asset('lottie/empty.json') ?>"></div>
            <h3>No matching records</h3>
            <p>Adjust the filters and search again.</p>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/panel_end.php'; ?>
