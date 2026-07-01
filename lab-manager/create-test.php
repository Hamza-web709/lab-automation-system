<?php
require_once __DIR__ . '/../includes/role_check.php';
requireRole('lab_manager');
$pdo = getPDO();

if (is_post()) {
    require_csrf();
    $productId = (int) ($_POST['product_id_ref'] ?? 0);
    $departmentId = (int) ($_POST['department_id'] ?? 0);
    $testingTypeId = (int) ($_POST['testing_type_id'] ?? 0);
    $assignedTesterId = (int) ($_POST['assigned_tester_id'] ?? 0);
    $testRollNo = trim($_POST['test_roll_no'] ?? '001');
    $testDate = $_POST['test_date'] ?: date('Y-m-d');
    $criteria = trim($_POST['criteria'] ?? '');
    $expectedOutput = trim($_POST['expected_output'] ?? '');
    $remarks = trim($_POST['detailed_remarks'] ?? '');
    $status = $_POST['status'] ?? 'pending';
    $nextAction = $_POST['next_action'] ?? 'none';

    try {
        $pdo->beginTransaction();
        $productStmt = $pdo->prepare('SELECT product_code, revise_no FROM products WHERE id = :id');
        $productStmt->execute(['id' => $productId]);
        $product = $productStmt->fetch();
        $typeStmt = $pdo->prepare('SELECT testing_code, department_id FROM testing_types WHERE id = :id');
        $typeStmt->execute(['id' => $testingTypeId]);
        $type = $typeStmt->fetch();
        $testerStmt = $pdo->prepare('SELECT id FROM users WHERE id = :id AND role = "tester" AND status = "active"');
        $testerStmt->execute(['id' => $assignedTesterId]);

        if (!$product || !$type || !$testerStmt->fetchColumn() || (int) $type['department_id'] !== $departmentId) {
            throw new RuntimeException('Invalid product, testing type, department, or tester selection.');
        }

        $testId = generateTestId($pdo, $product['product_code'], $product['revise_no'], $type['testing_code'], $testRollNo);
        $stmt = $pdo->prepare(
            'INSERT INTO testing_records
            (test_id, product_id_ref, testing_type_id, department_id, test_roll_no, test_date, criteria,
             expected_output, detailed_remarks, result, status, next_action, created_by, assigned_tester_id)
             VALUES
            (:test_id, :product_id_ref, :testing_type_id, :department_id, :test_roll_no, :test_date, :criteria,
             :expected_output, :detailed_remarks, "pending", :status, :next_action, :created_by, :assigned_tester_id)'
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
            'detailed_remarks' => $remarks,
            'status' => in_array($status, ['pending','in_progress','completed','sent_to_next_department','sent_to_cpri','sent_for_remaking'], true) ? $status : 'pending',
            'next_action' => in_array($nextAction, ['none','next_test','send_to_cpri','send_for_remaking'], true) ? $nextAction : 'none',
            'created_by' => current_user_id(),
            'assigned_tester_id' => $assignedTesterId,
        ]);
        syncProductStatusAfterTest($pdo, $productId, 'pending', $status, $nextAction);
        logActivity($pdo, current_user_id(), 'test_created', 'Lab Manager created test ' . $testId . '.');
        logActivity($pdo, current_user_id(), 'test_assigned', 'Assigned test ' . $testId . ' to tester #' . $assignedTesterId . '.');
        $pdo->commit();
        set_flash('success', 'Test created and assigned successfully. Test ID: ' . $testId);
        redirect('lab-manager/testing-records.php');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to create test.');
        redirect('lab-manager/create-test.php');
    }
}

$selectedProductId = (int) ($_GET['product_id'] ?? 0);
$products = $pdo->query('SELECT id, product_id, product_name FROM products ORDER BY created_at DESC')->fetchAll();
$departments = $pdo->query('SELECT id, name FROM testing_departments WHERE status = "active" ORDER BY name')->fetchAll();
$testingTypes = $pdo->query(
    'SELECT tt.*, td.name AS department_name, pt.name AS product_type_name
     FROM testing_types tt
     INNER JOIN testing_departments td ON td.id = tt.department_id
     INNER JOIN product_types pt ON pt.id = tt.product_type_id
     WHERE tt.status = "active"
     ORDER BY tt.name'
)->fetchAll();
$testers = $pdo->query('SELECT id, name, email FROM users WHERE role = "tester" AND status = "active" ORDER BY name')->fetchAll();
$pageTitle = 'Create Test';
$activePage = 'create-test';
$panelName = 'Lab Manager Panel';
$panelBrand = 'LabFlow';
include __DIR__ . '/../includes/panel_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div><p class="eyebrow">Assignment</p><h2>Create Testing Record</h2><p>Assign a test to an active tester and prefill criteria from the testing type.</p></div>
</div>
<form class="form-card surface-card" method="post" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="field"><label>Product <span class="required">*</span></label><select name="product_id_ref" required><option value="">Select product</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= optionSelected($selectedProductId, $product['id']) ?>><?= e($product['product_id']) ?> - <?= e($product['product_name']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Department <span class="required">*</span></label><select name="department_id" required><option value="">Select department</option><?php foreach ($departments as $department): ?><option value="<?= (int) $department['id'] ?>"><?= e($department['name']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Testing Type <span class="required">*</span></label><select name="testing_type_id" required data-testing-type><option value="">Select testing type</option><?php foreach ($testingTypes as $type): ?><option value="<?= (int) $type['id'] ?>" data-criteria="<?= e($type['criteria']) ?>" data-expected="<?= e($type['expected_output']) ?>"><?= e($type['name']) ?> - <?= e($type['department_name']) ?> / <?= e($type['product_type_name']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Assigned Tester <span class="required">*</span></label><select name="assigned_tester_id" required><option value="">Select tester</option><?php foreach ($testers as $tester): ?><option value="<?= (int) $tester['id'] ?>"><?= e($tester['name']) ?> - <?= e($tester['email']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Test Roll No <span class="required">*</span></label><input type="text" name="test_roll_no" value="001" required></div>
        <div class="field"><label>Test Date <span class="required">*</span></label><input type="date" name="test_date" value="<?= e(date('Y-m-d')) ?>" required></div>
        <div class="field"><label>Status</label><select name="status"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option><option value="sent_to_next_department">Sent to Next Department</option><option value="sent_to_cpri">Sent to CPRI</option><option value="sent_for_remaking">Sent for Re-making</option></select></div>
        <div class="field"><label>Next Action</label><select name="next_action"><option value="none">None</option><option value="next_test">Next Test</option><option value="send_to_cpri">Send to CPRI</option><option value="send_for_remaking">Send for Re-making</option></select></div>
        <div class="field span-2"><label>Criteria</label><textarea name="criteria" data-criteria></textarea></div>
        <div class="field span-2"><label>Expected Output</label><textarea name="expected_output" data-expected-output></textarea></div>
        <div class="field span-2"><label>Remarks</label><textarea name="detailed_remarks"></textarea></div>
    </div>
    <div class="form-actions"><button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Create & Assign</button><a class="btn btn-soft" href="<?= url('lab-manager/testing-records.php') ?>">Back</a></div>
</form>
<?php include __DIR__ . '/../includes/panel_end.php'; ?>
