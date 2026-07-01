<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$record = fetchTestRecordDetails($pdo, $id);
if (!$record) {
    set_flash('error', 'Testing record not found.');
    redirect('admin/tests/index.php');
}
$persons = fetchTestPersons($pdo, $id);
$pageTitle = 'Testing Record Details';
$activePage = 'tests';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Testing record</p>
        <h2><?= e($record['test_id']) ?></h2>
        <p><?= e($record['product_id']) ?> · <?= e($record['product_name']) ?></p>
    </div>
    <div class="form-actions">
        <a class="btn btn-primary" href="<?= url('admin/tests/report.php?id=' . (int) $record['id']) ?>"><i data-lucide="file-text"></i> Report</a>
        <a class="btn btn-soft" href="<?= url('admin/reports/print-report.php?id=' . (int) $record['id']) ?>"><i data-lucide="printer"></i> Print</a>
        <a class="btn btn-soft" href="<?= url('admin/tests/edit.php?id=' . (int) $record['id']) ?>"><i data-lucide="pencil"></i> Edit</a>
    </div>
</div>
<?php include __DIR__ . '/../../includes/test_report_card.php'; ?>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
