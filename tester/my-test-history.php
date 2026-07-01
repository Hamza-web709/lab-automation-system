<?php
require_once __DIR__ . '/../includes/role_check.php';
requireRole('tester');
$pdo = getPDO();
$stmt = $pdo->prepare(
    'SELECT tr.*, p.product_id, p.product_name, tt.name AS testing_type
     FROM testing_records tr
     INNER JOIN products p ON p.id = tr.product_id_ref
     INNER JOIN testing_types tt ON tt.id = tr.testing_type_id
     WHERE tr.assigned_tester_id = :id
     ORDER BY tr.updated_at DESC, tr.created_at DESC'
);
$stmt->execute(['id' => current_user_id()]);
$records = $stmt->fetchAll();
$pageTitle = 'My Test History';
$activePage = 'history';
$panelName = 'Tester Panel';
$panelBrand = 'LabFlow';
include __DIR__ . '/../includes/panel_start.php';
?>
<div class="page-header" data-animate="fade-up"><div><p class="eyebrow">History</p><h2>My Test History</h2><p>All testing records assigned to you.</p></div></div>
<section class="surface-card table-shell" data-animate="fade-up">
    <table class="data-table"><thead><tr><th>Test</th><th>Product</th><th>Result</th><th>Status</th><th>Report</th></tr></thead><tbody><?php foreach ($records as $record): ?><tr><td class="record-title"><strong><?= e($record['test_id']) ?></strong><span><?= e($record['testing_type']) ?></span></td><td><?= e($record['product_id']) ?> - <?= e($record['product_name']) ?></td><td><?= resultBadge($record['result']) ?></td><td><?= statusBadge($record['status']) ?></td><td><a class="icon-btn" href="<?= url('admin/reports/print-report.php?id=' . (int) $record['id']) ?>"><i data-lucide="printer"></i></a></td></tr><?php endforeach; ?></tbody></table>
</section>
<?php include __DIR__ . '/../includes/panel_end.php'; ?>
