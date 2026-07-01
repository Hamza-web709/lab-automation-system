<?php
require_once __DIR__ . '/../../includes/role_check.php';
requireAnyRole(['admin', 'lab_manager', 'tester']);
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$record = fetchTestRecordDetails($pdo, $id);
if (!$record) {
    set_flash('error', 'Testing record not found.');
    redirect(dashboardPathForRole(current_user()['role'] ?? null));
}
if (isTester() && (int) ($record['assigned_tester_id'] ?? 0) !== current_user_id()) {
    set_flash('error', 'You can only print reports assigned to you.');
    redirect('tester/my-test-history.php');
}
$persons = fetchTestPersons($pdo, $id);
$pageTitle = 'Print Test Report';
$activePage = isTester() ? 'history' : 'reports';
$panelName = match (current_user()['role'] ?? '') {
    'lab_manager' => 'Lab Manager Panel',
    'tester' => 'Tester Panel',
    default => 'Admin Panel',
};
$panelBrand = 'LabFlow';
include __DIR__ . '/../../includes/panel_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Printable report</p>
        <h2><?= e($record['test_id']) ?></h2>
        <p><?= e($record['product_id']) ?> · <?= e($record['product_name']) ?></p>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" type="button" onclick="window.print()"><i data-lucide="printer"></i> Print</button>
        <a class="btn btn-soft" href="<?= url(dashboardPathForRole(current_user()['role'] ?? null)) ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</div>
<?php include __DIR__ . '/../../includes/test_report_card.php'; ?>
<?php include __DIR__ . '/../../includes/panel_end.php'; ?>
