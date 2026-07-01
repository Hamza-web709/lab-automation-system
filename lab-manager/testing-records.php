<?php
require_once __DIR__ . '/../includes/role_check.php';
requireRole('lab_manager');
$pdo = getPDO();
$pageTitle = 'Testing Records';
$activePage = 'tests';
$panelName = 'Lab Manager Panel';
$panelBrand = 'LabFlow';
$records = $pdo->query(
    'SELECT tr.*, p.product_id, p.product_name, tt.name AS testing_type, td.name AS department, assigned.name AS assigned_tester
     FROM testing_records tr
     INNER JOIN products p ON p.id = tr.product_id_ref
     INNER JOIN testing_types tt ON tt.id = tr.testing_type_id
     INNER JOIN testing_departments td ON td.id = tr.department_id
     LEFT JOIN users assigned ON assigned.id = tr.assigned_tester_id
     ORDER BY tr.test_date DESC, tr.created_at DESC'
)->fetchAll();
include __DIR__ . '/../includes/panel_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div><p class="eyebrow">Workflow</p><h2>Testing Records</h2><p>Review test progress and update status without delete permissions.</p></div>
    <a class="btn btn-primary" href="<?= url('lab-manager/create-test.php') ?>"><i data-lucide="clipboard-plus"></i> Create Test</a>
</div>
<section class="surface-card table-shell" data-animate="fade-up">
    <?php if ($records): ?>
        <table class="data-table">
            <thead><tr><th>Test</th><th>Product</th><th>Department</th><th>Tester</th><th>Result</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td class="record-title"><strong><?= e($record['test_id']) ?></strong><span><?= e($record['testing_type']) ?></span></td>
                        <td class="record-title"><strong><?= e($record['product_id']) ?></strong><span><?= e($record['product_name']) ?></span></td>
                        <td><?= e($record['department']) ?></td>
                        <td><?= e($record['assigned_tester'] ?: 'Not assigned') ?></td>
                        <td><?= resultBadge($record['result']) ?></td>
                        <td><?= statusBadge($record['status']) ?></td>
                        <td><div class="table-actions"><a class="icon-btn" href="<?= url('lab-manager/update-test-status.php?id=' . (int) $record['id']) ?>" aria-label="Update"><i data-lucide="settings-2"></i></a><a class="icon-btn" href="<?= url('admin/reports/print-report.php?id=' . (int) $record['id']) ?>" aria-label="Print"><i data-lucide="printer"></i></a></div></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state"><div class="empty-lottie" data-lottie="<?= asset('lottie/empty.json') ?>"></div><h3>No testing records</h3><p>Create and assign a test to begin.</p></div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/panel_end.php'; ?>
