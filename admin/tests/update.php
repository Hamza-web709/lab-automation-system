<?php
require_once __DIR__ . '/../../includes/auth_check.php';

if (!is_post()) {
    redirect('admin/tests/index.php');
}

require_csrf();
$pdo = getPDO();
$id = (int) ($_POST['id'] ?? 0);
$productId = (int) ($_POST['product_id_ref'] ?? 0);
$departmentId = (int) ($_POST['department_id'] ?? 0);
$testingTypeId = (int) ($_POST['testing_type_id'] ?? 0);
$testRollNo = trim($_POST['test_roll_no'] ?? '');
$testDate = $_POST['test_date'] ?: date('Y-m-d');
$criteria = trim($_POST['criteria'] ?? '');
$expectedOutput = trim($_POST['expected_output'] ?? '');
$observedOutput = trim($_POST['observed_output'] ?? '');
$detailedRemarks = trim($_POST['detailed_remarks'] ?? '');
$result = $_POST['result'] ?? 'pending';
$status = $_POST['status'] ?? 'pending';
$nextAction = $_POST['next_action'] ?? 'none';
$validResults = ['pass', 'fail', 'pending'];
$validStatuses = ['pending', 'in_progress', 'completed', 'sent_to_next_department', 'sent_to_cpri', 'sent_for_remaking'];
$validActions = ['none', 'next_test', 'send_to_cpri', 'send_for_remaking'];

if ($id <= 0 || $productId <= 0 || $departmentId <= 0 || $testingTypeId <= 0 || $testRollNo === '' || !in_array($result, $validResults, true) || !in_array($status, $validStatuses, true) || !in_array($nextAction, $validActions, true)) {
    set_flash('error', 'Please complete the required testing record fields.');
    redirect('admin/tests/index.php');
}

try {
    $pdo->beginTransaction();

    $typeStmt = $pdo->prepare('SELECT department_id FROM testing_types WHERE id = :id LIMIT 1');
    $typeStmt->execute(['id' => $testingTypeId]);
    $typeDepartment = (int) $typeStmt->fetchColumn();
    if ($typeDepartment !== $departmentId) {
        throw new RuntimeException('Selected testing type does not belong to the selected department.');
    }

    // Test ID remains unchanged during update; the editable roll number is retained for report detail.
    $stmt = $pdo->prepare(
        'UPDATE testing_records
         SET product_id_ref = :product_id_ref, testing_type_id = :testing_type_id, department_id = :department_id,
             test_roll_no = :test_roll_no, test_date = :test_date, criteria = :criteria,
             expected_output = :expected_output, observed_output = :observed_output,
             detailed_remarks = :detailed_remarks, result = :result, status = :status, next_action = :next_action
         WHERE id = :id'
    );
    $stmt->execute([
        'product_id_ref' => $productId,
        'testing_type_id' => $testingTypeId,
        'department_id' => $departmentId,
        'test_roll_no' => $testRollNo,
        'test_date' => $testDate,
        'criteria' => $criteria,
        'expected_output' => $expectedOutput,
        'observed_output' => $observedOutput,
        'detailed_remarks' => $detailedRemarks,
        'result' => $result,
        'status' => $status,
        'next_action' => $nextAction,
        'id' => $id,
    ]);

    $pdo->prepare('DELETE FROM test_persons WHERE testing_record_id = :id')->execute(['id' => $id]);
    saveTestPersons($pdo, $id, $_POST['person_name'] ?? [], $_POST['designation'] ?? [], $_POST['person_remarks'] ?? []);
    syncProductStatusAfterTest($pdo, $productId, $result, $status, $nextAction);
    logActivity($pdo, current_user_id(), 'testing_record_updated', 'Updated testing record #' . $id . '.');
    $pdo->commit();

    set_flash('success', 'Testing record updated successfully.');
    redirect('admin/tests/view.php?id=' . $id);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to update testing record.');
    redirect('admin/tests/edit.php?id=' . $id);
}
