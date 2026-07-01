<?php
require_once __DIR__ . '/../../includes/auth_check.php';

if (!is_post()) {
    redirect('admin/tests/index.php');
}

require_csrf();
$pdo = getPDO();
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

if ($productId <= 0 || $departmentId <= 0 || $testingTypeId <= 0 || $testRollNo === '' || !in_array($result, $validResults, true) || !in_array($status, $validStatuses, true) || !in_array($nextAction, $validActions, true)) {
    set_flash('error', 'Please complete the required testing record fields.');
    redirect('admin/tests/create.php');
}

try {
    $pdo->beginTransaction();

    $productStmt = $pdo->prepare('SELECT id, product_code, revise_no FROM products WHERE id = :id LIMIT 1');
    $productStmt->execute(['id' => $productId]);
    $product = $productStmt->fetch();

    $typeStmt = $pdo->prepare('SELECT id, testing_code, department_id FROM testing_types WHERE id = :id LIMIT 1');
    $typeStmt->execute(['id' => $testingTypeId]);
    $testingType = $typeStmt->fetch();

    if (!$product || !$testingType || (int) $testingType['department_id'] !== $departmentId) {
        throw new RuntimeException('Selected product, department, or testing type is invalid.');
    }

    $testId = generateTestId($pdo, $product['product_code'], $product['revise_no'], $testingType['testing_code'], $testRollNo);

    // Insert the testing record first, then attach tester persons inside the same transaction.
    $stmt = $pdo->prepare(
        'INSERT INTO testing_records
        (test_id, product_id_ref, testing_type_id, department_id, test_roll_no, test_date, criteria,
         expected_output, observed_output, detailed_remarks, result, status, next_action, created_by)
        VALUES
        (:test_id, :product_id_ref, :testing_type_id, :department_id, :test_roll_no, :test_date, :criteria,
         :expected_output, :observed_output, :detailed_remarks, :result, :status, :next_action, :created_by)'
    );
    $stmt->execute([
        'test_id' => $testId,
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
        'created_by' => current_user_id(),
    ]);

    $recordId = (int) $pdo->lastInsertId();
    saveTestPersons($pdo, $recordId, $_POST['person_name'] ?? [], $_POST['designation'] ?? [], $_POST['person_remarks'] ?? []);
    syncProductStatusAfterTest($pdo, $productId, $result, $status, $nextAction);
    logActivity($pdo, current_user_id(), 'testing_record_created', 'Created testing record ' . $testId . '.');
    $pdo->commit();

    set_flash('success', 'Testing record created with ID ' . $testId . '.');
    redirect('admin/tests/view.php?id=' . $recordId);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to create testing record.');
    redirect('admin/tests/create.php');
}
