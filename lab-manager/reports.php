<?php
require_once __DIR__ . '/../includes/role_check.php';
requireRole('lab_manager');
$pdo = getPDO();
$pageTitle = 'Reports';
$activePage = 'reports';
$panelName = 'Lab Manager Panel';
$panelBrand = 'LabFlow';
$records = $pdo->query(
    'SELECT tr.*, p.product_id, p.product_name, tt.name AS testing_type
     FROM testing_records tr
     INNER JOIN products p ON p.id = tr.product_id_ref
     INNER JOIN testing_types tt ON tt.id = tr.testing_type_id
     ORDER BY tr.test_date DESC'
)->fetchAll();
include __DIR__ . '/../includes/panel_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div><p class="eyebrow">Reports</p><h2>Workflow Reports</h2><p>View and print testing workflow reports.</p></div>
</div>
<section class="surface-card table-shell" data-animate="fade-up">
    <table class="data-table"><thead><tr><th>Test</th><th>Product</th><th>Type</th><th>Result</th><th>Status</th><th>Print</th></tr></thead><tbody>
        <?php foreach ($records as $record): ?><tr><td><?= e($record['test_id']) ?></td><td><?= e($record['product_id']) ?> - <?= e($record['product_name']) ?></td><td><?= e($record['testing_type']) ?></td><td><?= resultBadge($record['result']) ?></td><td><?= statusBadge($record['status']) ?></td><td><a class="icon-btn" href="<?= url('admin/reports/print-report.php?id=' . (int) $record['id']) ?>"><i data-lucide="printer"></i></a></td></tr><?php endforeach; ?>
    </tbody></table>
</section>
<?php include __DIR__ . '/../includes/panel_end.php'; ?>
