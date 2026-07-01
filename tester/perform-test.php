<?php
require_once __DIR__ . '/../includes/role_check.php';
requireRole('tester');
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$record = fetchTestRecordDetails($pdo, $id);
if (!$record || (int) ($record['assigned_tester_id'] ?? 0) !== current_user_id()) {
    set_flash('error', 'You can only open tests assigned to you.');
    redirect('tester/assigned-tests.php');
}
$pageTitle = 'Perform Test';
$activePage = 'assigned-tests';
$panelName = 'Tester Panel';
$panelBrand = 'LabFlow';
include __DIR__ . '/../includes/panel_start.php';
?>
<div class="page-header" data-animate="fade-up"><div><p class="eyebrow">Submit result</p><h2><?= e($record['test_id']) ?></h2><p><?= e($record['product_id']) ?> · <?= e($record['product_name']) ?></p></div></div>
<section class="report-paper surface-card" data-animate="fade-up">
    <div class="report-grid">
        <?php foreach (['Product ID'=>$record['product_id'],'Product Name'=>$record['product_name'],'Product Type'=>$record['product_type_name'],'Department'=>$record['department_name'],'Testing Type'=>$record['testing_type_name'],'Test Date'=>formatDate($record['test_date']),'Current Status'=>humanize($record['status'])] as $label => $value): ?>
            <div class="report-field"><span><?= e($label) ?></span><strong><?= e($value) ?></strong></div>
        <?php endforeach; ?>
        <div class="report-field" style="grid-column:1 / -1;"><span>Criteria</span><p><?= nl2br(e($record['criteria'] ?: '-')) ?></p></div>
        <div class="report-field" style="grid-column:1 / -1;"><span>Expected Output</span><p><?= nl2br(e($record['expected_output'] ?: '-')) ?></p></div>
    </div>
</section>
<form class="form-card surface-card" method="post" action="<?= url('tester/submit-result.php') ?>" data-validate data-animate="fade-up" style="margin-top:18px;">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
    <div class="form-grid">
        <div class="field span-2"><label>Observed Output</label><textarea name="observed_output"><?= e($record['observed_output']) ?></textarea></div>
        <div class="field span-2"><label>Detailed Remarks</label><textarea name="detailed_remarks"><?= e($record['detailed_remarks']) ?></textarea></div>
        <div class="field"><label>Result</label><select name="result"><option value="pending" <?= optionSelected($record['result'], 'pending') ?>>Pending</option><option value="pass" <?= optionSelected($record['result'], 'pass') ?>>Pass</option><option value="fail" <?= optionSelected($record['result'], 'fail') ?>>Fail</option></select></div>
        <div class="field"><label>Status</label><select name="status"><option value="in_progress" <?= optionSelected($record['status'], 'in_progress') ?>>In Progress</option><option value="completed" <?= optionSelected($record['status'], 'completed') ?>>Completed</option></select></div>
    </div>
    <div class="form-actions"><button class="btn btn-primary" type="submit"><i data-lucide="send"></i> Submit Result</button><a class="btn btn-soft" href="<?= url('tester/assigned-tests.php') ?>">Back</a></div>
</form>
<?php include __DIR__ . '/../includes/panel_end.php'; ?>
