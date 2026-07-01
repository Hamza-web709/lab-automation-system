<?php
require_once __DIR__ . '/../includes/role_check.php';
requireRole('lab_manager');
$pdo = getPDO();
$pageTitle = 'Lab Manager Dashboard';
$activePage = 'dashboard';
$panelName = 'Lab Manager Panel';
$panelBrand = 'LabFlow';

$stats = [
    'under_testing' => tableCount($pdo, 'products', 'current_status = :status', ['status' => 'under_testing']),
    'pending_tests' => tableCount($pdo, 'testing_records', 'status = :status', ['status' => 'pending']),
    'in_progress' => tableCount($pdo, 'testing_records', 'status = :status', ['status' => 'in_progress']),
    'completed' => tableCount($pdo, 'testing_records', 'status = :status', ['status' => 'completed']),
    'failed' => tableCount($pdo, 'testing_records', 'result = :result', ['result' => 'fail']),
    'passed' => tableCount($pdo, 'testing_records', 'result = :result', ['result' => 'pass']),
    'sent_to_cpri' => tableCount($pdo, 'products', 'current_status = :status', ['status' => 'sent_to_cpri']),
    'sent_for_remaking' => tableCount($pdo, 'products', 'current_status = :status', ['status' => 'sent_for_remaking']),
];

$recent = $pdo->query(
    'SELECT tr.*, p.product_id, p.product_name, tt.name AS testing_type, assigned.name AS assigned_tester
     FROM testing_records tr
     INNER JOIN products p ON p.id = tr.product_id_ref
     INNER JOIN testing_types tt ON tt.id = tr.testing_type_id
     LEFT JOIN users assigned ON assigned.id = tr.assigned_tester_id
     ORDER BY tr.created_at DESC
     LIMIT 8'
)->fetchAll();

include __DIR__ . '/../includes/panel_start.php';
?>
<section class="dashboard-hero surface-card" data-animate="fade-up">
    <div>
        <p class="eyebrow">Testing workflow</p>
        <h2>Manage tests, assignments, and next actions.</h2>
        <p>Create records, assign testers, monitor status, and move products toward CPRI or re-making decisions.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('lab-manager/create-test.php') ?>"><i data-lucide="clipboard-plus"></i> Create New Test</a>
</section>

<section class="stats-grid">
    <?php
    $cards = [
        ['activity', 'Under Testing', $stats['under_testing']],
        ['clock', 'Pending Tests', $stats['pending_tests']],
        ['loader-circle', 'In Progress', $stats['in_progress']],
        ['check-circle-2', 'Completed', $stats['completed']],
        ['x-circle', 'Failed Tests', $stats['failed']],
        ['badge-check', 'Passed Tests', $stats['passed']],
        ['send', 'Sent to CPRI', $stats['sent_to_cpri']],
        ['refresh-cw', 'Re-making', $stats['sent_for_remaking']],
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

<section class="panel surface-card" data-animate="fade-up">
    <div class="panel-header">
        <h3>Recent Assigned Tests</h3>
        <a class="btn btn-soft" href="<?= url('lab-manager/testing-records.php') ?>"><i data-lucide="clipboard-list"></i> View All</a>
    </div>
    <?php if ($recent): ?>
        <div class="table-shell">
            <table class="data-table">
                <thead><tr><th>Test</th><th>Product</th><th>Assigned Tester</th><th>Result</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($recent as $record): ?>
                        <tr>
                            <td class="record-title"><strong><?= e($record['test_id']) ?></strong><span><?= e($record['testing_type']) ?></span></td>
                            <td class="record-title"><strong><?= e($record['product_id']) ?></strong><span><?= e($record['product_name']) ?></span></td>
                            <td><?= e($record['assigned_tester'] ?: 'Not assigned') ?></td>
                            <td><?= resultBadge($record['result']) ?></td>
                            <td><?= statusBadge($record['status']) ?></td>
                            <td><?= e(formatDate($record['test_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state"><div class="empty-lottie" data-lottie="<?= asset('lottie/empty.json') ?>"></div><h3>No tests yet</h3><p>Create and assign the first test record.</p></div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/panel_end.php'; ?>
