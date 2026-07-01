<?php
require_once __DIR__ . '/../includes/role_check.php';
requireRole('lab_manager');
$pdo = getPDO();

if (is_post()) {
    require_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    $nextAction = $_POST['next_action'] ?? 'none';
    $validStatuses = ['pending','in_progress','completed','sent_to_next_department','sent_to_cpri','sent_for_remaking'];
    $validActions = ['none','next_test','send_to_cpri','send_for_remaking'];

    if ($id > 0 && in_array($status, $validStatuses, true) && in_array($nextAction, $validActions, true)) {
        $record = fetchTestRecordDetails($pdo, $id);
        if ($record) {
            $stmt = $pdo->prepare('UPDATE testing_records SET status = :status, next_action = :next_action WHERE id = :id');
            $stmt->execute(['status' => $status, 'next_action' => $nextAction, 'id' => $id]);
            syncProductStatusAfterTest($pdo, (int) $record['product_id_ref'], $record['result'], $status, $nextAction);
            logActivity($pdo, current_user_id(), 'test_status_updated', 'Updated status for test ' . $record['test_id'] . '.');
            set_flash('success', 'Test status updated.');
        }
    }
    redirect('lab-manager/update-test-status.php?id=' . $id);
}

$id = (int) ($_GET['id'] ?? 0);
$record = $id ? fetchTestRecordDetails($pdo, $id) : null;
$records = $pdo->query(
    'SELECT tr.id, tr.test_id, tr.status, tr.result, p.product_id, p.product_name
     FROM testing_records tr
     INNER JOIN products p ON p.id = tr.product_id_ref
     ORDER BY tr.created_at DESC LIMIT 20'
)->fetchAll();
$pageTitle = 'Update Test Status';
$activePage = 'progress';
$panelName = 'Lab Manager Panel';
$panelBrand = 'LabFlow';
include __DIR__ . '/../includes/panel_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div><p class="eyebrow">Progress</p><h2>Update Test Status</h2><p>Control workflow status and next action for testing records.</p></div>
</div>
<?php if ($record): ?>
    <form class="form-card surface-card" method="post" data-animate="fade-up">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
        <div class="form-grid">
            <div class="field"><label>Test ID</label><input type="text" value="<?= e($record['test_id']) ?>" disabled></div>
            <div class="field"><label>Product</label><input type="text" value="<?= e($record['product_id'] . ' - ' . $record['product_name']) ?>" disabled></div>
            <div class="field"><label>Status</label><select name="status"><?php foreach (['pending','in_progress','completed','sent_to_next_department','sent_to_cpri','sent_for_remaking'] as $item): ?><option value="<?= e($item) ?>" <?= optionSelected($record['status'], $item) ?>><?= e(humanize($item)) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label>Next Action</label><select name="next_action"><?php foreach (['none','next_test','send_to_cpri','send_for_remaking'] as $item): ?><option value="<?= e($item) ?>" <?= optionSelected($record['next_action'], $item) ?>><?= e(humanize($item)) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="form-actions"><button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Update Status</button></div>
    </form>
<?php endif; ?>
<section class="panel surface-card" data-animate="fade-up" style="margin-top:18px;">
    <div class="panel-header"><h3>Recent Tests</h3></div>
    <div class="table-shell"><table class="data-table"><thead><tr><th>Test</th><th>Product</th><th>Result</th><th>Status</th><th>Update</th></tr></thead><tbody><?php foreach ($records as $row): ?><tr><td><?= e($row['test_id']) ?></td><td><?= e($row['product_id']) ?> - <?= e($row['product_name']) ?></td><td><?= resultBadge($row['result']) ?></td><td><?= statusBadge($row['status']) ?></td><td><a class="btn btn-soft" href="<?= url('lab-manager/update-test-status.php?id=' . (int) $row['id']) ?>">Open</a></td></tr><?php endforeach; ?></tbody></table></div>
</section>
<?php include __DIR__ . '/../includes/panel_end.php'; ?>
