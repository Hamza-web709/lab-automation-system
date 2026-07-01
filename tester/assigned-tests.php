<?php
require_once __DIR__ . '/../includes/role_check.php';
requireRole('tester');
$pdo = getPDO();
$stmt = $pdo->prepare(
    'SELECT tr.*, p.product_id, p.product_name, tt.name AS testing_type, td.name AS department
     FROM testing_records tr
     INNER JOIN products p ON p.id = tr.product_id_ref
     INNER JOIN testing_types tt ON tt.id = tr.testing_type_id
     INNER JOIN testing_departments td ON td.id = tr.department_id
     WHERE tr.assigned_tester_id = :id AND tr.status <> "completed"
     ORDER BY tr.test_date ASC, tr.created_at DESC'
);
$stmt->execute(['id' => current_user_id()]);
$records = $stmt->fetchAll();
$pageTitle = 'Assigned Tests';
$activePage = 'assigned-tests';
$panelName = 'Tester Panel';
$panelBrand = 'LabFlow';
include __DIR__ . '/../includes/panel_start.php';
?>
<div class="page-header" data-animate="fade-up"><div><p class="eyebrow">Assignments</p><h2>Assigned Tests</h2><p>Only tests assigned to your account are visible here.</p></div></div>
<section class="surface-card table-shell" data-animate="fade-up">
    <?php if ($records): ?><table class="data-table"><thead><tr><th>Test</th><th>Product</th><th>Department</th><th>Result</th><th>Status</th><th>Perform</th></tr></thead><tbody><?php foreach ($records as $record): ?><tr><td class="record-title"><strong><?= e($record['test_id']) ?></strong><span><?= e($record['testing_type']) ?></span></td><td><?= e($record['product_id']) ?> - <?= e($record['product_name']) ?></td><td><?= e($record['department']) ?></td><td><?= resultBadge($record['result']) ?></td><td><?= statusBadge($record['status']) ?></td><td><a class="btn btn-primary" href="<?= url('tester/perform-test.php?id=' . (int) $record['id']) ?>"><i data-lucide="play"></i> Perform</a></td></tr><?php endforeach; ?></tbody></table><?php else: ?><div class="empty-state"><div class="empty-lottie" data-lottie="<?= asset('lottie/empty.json') ?>"></div><h3>No active assignments</h3><p>Completed tests move into your history.</p></div><?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/panel_end.php'; ?>
