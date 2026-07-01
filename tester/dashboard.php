<?php
require_once __DIR__ . '/../includes/role_check.php';
requireRole('tester');
$pdo = getPDO();
$testerId = current_user_id();
$pageTitle = 'Tester Dashboard';
$activePage = 'dashboard';
$panelName = 'Tester Panel';
$panelBrand = 'LabFlow';
$stats = [
    'assigned' => tableCount($pdo, 'testing_records', 'assigned_tester_id = :id', ['id' => $testerId]),
    'pending' => tableCount($pdo, 'testing_records', 'assigned_tester_id = :id AND status = :status', ['id' => $testerId, 'status' => 'pending']),
    'in_progress' => tableCount($pdo, 'testing_records', 'assigned_tester_id = :id AND status = :status', ['id' => $testerId, 'status' => 'in_progress']),
    'completed' => tableCount($pdo, 'testing_records', 'assigned_tester_id = :id AND status = :status', ['id' => $testerId, 'status' => 'completed']),
    'passed' => tableCount($pdo, 'testing_records', 'assigned_tester_id = :id AND result = :result', ['id' => $testerId, 'result' => 'pass']),
    'failed' => tableCount($pdo, 'testing_records', 'assigned_tester_id = :id AND result = :result', ['id' => $testerId, 'result' => 'fail']),
];
$recentStmt = $pdo->prepare(
    'SELECT tr.*, p.product_id, p.product_name, tt.name AS testing_type
     FROM testing_records tr
     INNER JOIN products p ON p.id = tr.product_id_ref
     INNER JOIN testing_types tt ON tt.id = tr.testing_type_id
     WHERE tr.assigned_tester_id = :id
     ORDER BY tr.updated_at DESC, tr.created_at DESC
     LIMIT 8'
);
$recentStmt->execute(['id' => $testerId]);
$recent = $recentStmt->fetchAll();
include __DIR__ . '/../includes/panel_start.php';
?>
<section class="dashboard-hero surface-card" data-animate="fade-up">
    <div><p class="eyebrow">My testing work</p><h2>Open assigned tests and submit results.</h2><p>You can only see and update tests assigned to your account.</p></div>
    <a class="btn btn-primary" href="<?= url('tester/assigned-tests.php') ?>"><i data-lucide="clipboard-check"></i> View Assigned Tests</a>
</section>
<section class="stats-grid">
    <?php foreach ([['clipboard-check','Assigned',$stats['assigned']],['clock','Pending',$stats['pending']],['loader-circle','In Progress',$stats['in_progress']],['check-circle-2','Completed',$stats['completed']],['badge-check','Passed',$stats['passed']],['x-circle','Failed',$stats['failed']]] as $card): ?>
        <article class="stat-card surface-card" data-animate="fade-up"><span class="stat-icon"><i data-lucide="<?= e($card[0]) ?>"></i></span><strong data-count="<?= (int) $card[2] ?>">0</strong><span><?= e($card[1]) ?></span></article>
    <?php endforeach; ?>
</section>
<section class="panel surface-card" data-animate="fade-up">
    <div class="panel-header"><h3>Recent Test History</h3></div>
    <?php if ($recent): ?><div class="table-shell"><table class="data-table"><thead><tr><th>Test</th><th>Product</th><th>Result</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach ($recent as $record): ?><tr><td class="record-title"><strong><?= e($record['test_id']) ?></strong><span><?= e($record['testing_type']) ?></span></td><td><?= e($record['product_id']) ?> - <?= e($record['product_name']) ?></td><td><?= resultBadge($record['result']) ?></td><td><?= statusBadge($record['status']) ?></td><td><a class="btn btn-soft" href="<?= url('tester/perform-test.php?id=' . (int) $record['id']) ?>">Open</a></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="empty-state"><div class="empty-lottie" data-lottie="<?= asset('lottie/empty.json') ?>"></div><h3>No assigned tests</h3><p>Your assigned tests will appear here.</p></div><?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/panel_end.php'; ?>
