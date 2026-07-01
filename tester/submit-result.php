<?php
require_once __DIR__ . '/../includes/role_check.php';
requireRole('tester');

if (!is_post()) {
    redirect('tester/assigned-tests.php');
}

require_csrf();
$pdo = getPDO();
$id = (int) ($_POST['id'] ?? 0);
$record = fetchTestRecordDetails($pdo, $id);
if (!$record || (int) ($record['assigned_tester_id'] ?? 0) !== current_user_id()) {
    set_flash('error', 'You can only update tests assigned to you.');
    redirect('tester/assigned-tests.php');
}

$result = in_array($_POST['result'] ?? 'pending', ['pass','fail','pending'], true) ? $_POST['result'] : 'pending';
$status = in_array($_POST['status'] ?? 'in_progress', ['in_progress','completed'], true) ? $_POST['status'] : 'in_progress';
$observed = trim($_POST['observed_output'] ?? '');
$remarks = trim($_POST['detailed_remarks'] ?? '');

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare(
        'UPDATE testing_records
         SET observed_output = :observed_output, detailed_remarks = :detailed_remarks,
             result = :result, status = :status
         WHERE id = :id AND assigned_tester_id = :tester_id'
    );
    $stmt->execute([
        'observed_output' => $observed,
        'detailed_remarks' => $remarks,
        'result' => $result,
        'status' => $status,
        'id' => $id,
        'tester_id' => current_user_id(),
    ]);
    saveTestPersons($pdo, $id, [current_user()['name']], [humanize(current_user()['role'])], ['Submitted result via tester panel.']);
    syncProductStatusAfterTest($pdo, (int) $record['product_id_ref'], $result, $status, $record['next_action']);
    logActivity($pdo, current_user_id(), 'test_result_submitted', 'Submitted result for test ' . $record['test_id'] . '.');
    $pdo->commit();
    set_flash('success', 'Test result submitted successfully.');
    redirect($status === 'completed' ? 'tester/my-test-history.php' : 'tester/assigned-tests.php');
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to submit test result.');
    redirect('tester/perform-test.php?id=' . $id);
}
