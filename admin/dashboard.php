<?php
require_once __DIR__ . '/../includes/auth_check.php';

$pdo = getPDO();
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// Dashboard counters are intentionally simple aggregate queries so they stay fast and easy to audit.
$stats = [
    'total_products' => tableCount($pdo, 'products'),
    'total_testing_records' => tableCount($pdo, 'testing_records'),
    'under_testing' => tableCount($pdo, 'products', 'current_status = :status', ['status' => 'under_testing']),
    'passed_tests' => tableCount($pdo, 'testing_records', 'result = :result', ['result' => 'pass']),
    'failed_tests' => tableCount($pdo, 'testing_records', 'result = :result', ['result' => 'fail']),
    'pending_tests' => tableCount($pdo, 'testing_records', 'result = :result', ['result' => 'pending']),
    'sent_to_cpri' => tableCount($pdo, 'products', 'current_status = :status', ['status' => 'sent_to_cpri']),
    'sent_for_remaking' => tableCount($pdo, 'products', 'current_status = :status', ['status' => 'sent_for_remaking']),
    'lab_managers' => tableCount($pdo, 'users', 'role = :role', ['role' => 'lab_manager']),
    'testers' => tableCount($pdo, 'users', 'role = :role', ['role' => 'tester']),
];

$recentStmt = $pdo->query(
    'SELECT tr.*, p.product_id, p.product_name, tt.name AS testing_type, td.name AS department
     FROM testing_records tr
     INNER JOIN products p ON p.id = tr.product_id_ref
     INNER JOIN testing_types tt ON tt.id = tr.testing_type_id
     INNER JOIN testing_departments td ON td.id = tr.department_id
     ORDER BY tr.created_at DESC
     LIMIT 8'
);
$recentRecords = $recentStmt->fetchAll();

$activityStmt = $pdo->query(
    'SELECT al.*, u.name AS user_name
     FROM activity_logs al
     LEFT JOIN users u ON u.id = al.user_id
     ORDER BY al.created_at DESC
     LIMIT 6'
);
$activityLogs = $activityStmt->fetchAll();

include __DIR__ . '/../includes/admin_start.php';
?>
<section class="dashboard-hero surface-card" data-animate="fade-up">
    <div>
        <p class="eyebrow">Workflow overview</p>
        <h2>Good to see you, <?= e($_SESSION['user_name'] ?? 'Lab Team') ?>.</h2>
        <p>Track manufactured products, testing outcomes, CPRI movement, and re-making decisions from one operational view.</p>
    </div>
    <div class="dashboard-lottie" data-lottie="<?= asset('lottie/lab-hero.json') ?>"></div>
</section>

<section class="stats-grid">
    <?php
    $cards = [
        ['package', 'Total Products', $stats['total_products'], 'primary'],
        ['clipboard-list', 'Testing Records', $stats['total_testing_records'], 'primary'],
        ['clock', 'Pending Tests', $stats['pending_tests'], 'warning'],
        ['check-circle-2', 'Passed Tests', $stats['passed_tests'], 'success'],
        ['x-circle', 'Failed Tests', $stats['failed_tests'], 'danger'],
        ['send', 'Sent to CPRI', $stats['sent_to_cpri'], 'info'],
        ['refresh-cw', 'Re-making', $stats['sent_for_remaking'], 'danger'],
        ['user-cog', 'Lab Managers', $stats['lab_managers'], 'primary'],
        ['user-check', 'Testers', $stats['testers'], 'success'],
    ];
    foreach ($cards as $card):
    ?>
        <article class="stat-card surface-card" data-animate="fade-up">
            <span class="stat-icon"><i data-lucide="<?= e($card[0]) ?>"></i></span>
            <strong data-count="<?= (int) $card[2] ?>">0</strong>
            <span><?= e($card[1]) ?></span>
        </article>
    <?php endforeach; ?>
</section>

<section class="dashboard-grid">
    <div class="panel surface-card" data-animate="fade-up">
        <div class="panel-header">
            <h3>Recent Testing Records</h3>
            <a class="btn btn-soft" href="<?= url('admin/tests/index.php') ?>"><i data-lucide="clipboard-list"></i> View All</a>
        </div>
        <?php if ($recentRecords): ?>
            <div class="table-shell">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Test</th>
                            <th>Product</th>
                            <th>Department</th>
                            <th>Result</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRecords as $record): ?>
                            <tr data-animate="fade-up">
                                <td><strong><?= e($record['test_id']) ?></strong><br><span><?= e($record['testing_type']) ?></span></td>
                                <td><?= e($record['product_id']) ?><br><span><?= e($record['product_name']) ?></span></td>
                                <td><?= e($record['department']) ?></td>
                                <td><?= resultBadge($record['result']) ?></td>
                                <td><?= statusBadge($record['status']) ?></td>
                                <td><?= e(formatDate($record['test_date'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-lottie" data-lottie="<?= asset('lottie/empty.json') ?>"></div>
                <h3>No testing records yet</h3>
                <p>Create a product and add its first testing record to populate the lab activity stream.</p>
                <a class="btn btn-primary" href="<?= url('admin/tests/create.php') ?>"><i data-lucide="plus"></i> Add Test Record</a>
            </div>
        <?php endif; ?>
    </div>

    <aside class="panel surface-card" data-animate="fade-up">
        <div class="panel-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="quick-actions">
            <a class="quick-action" href="<?= url('admin/products/create.php') ?>">
                <div><strong>Add Product</strong><span>Register manufactured item</span></div>
                <i data-lucide="plus"></i>
            </a>
            <a class="quick-action" href="<?= url('admin/tests/create.php') ?>">
                <div><strong>Create Test</strong><span>Record testing outcome</span></div>
                <i data-lucide="clipboard-plus"></i>
            </a>
            <a class="quick-action" href="<?= url('admin/search/advanced-search.php') ?>">
                <div><strong>Advanced Search</strong><span>Find products and tests</span></div>
                <i data-lucide="search"></i>
            </a>
            <a class="quick-action" href="<?= url('admin/reports/index.php') ?>">
                <div><strong>Reports</strong><span>Review printable output</span></div>
                <i data-lucide="file-text"></i>
            </a>
        </div>

        <div class="panel-header" style="margin-top: 24px;">
            <h3>Activity</h3>
        </div>
        <div class="activity-list">
            <?php foreach ($activityLogs as $log): ?>
                <div class="activity-item">
                    <strong><?= e(humanize($log['action'])) ?></strong>
                    <span><?= e($log['user_name'] ?: 'System') ?> - <?= e(formatDate($log['created_at'], 'd M Y, h:i A')) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </aside>
</section>
<?php include __DIR__ . '/../includes/admin_end.php'; ?>
