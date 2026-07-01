<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin', 'lab_manager']);

if (!is_post()) {
    redirect('admin/testing-types/index.php');
}

require_csrf();
$pdo = getPDO();
$id = (int) ($_POST['id'] ?? 0);
$departmentId = (int) ($_POST['department_id'] ?? 0);
$productTypeId = (int) ($_POST['product_type_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$testingCode = normalizeCodePart($_POST['testing_code'] ?? '', 10);
$description = trim($_POST['description'] ?? '');
$criteria = trim($_POST['criteria'] ?? '');
$expectedOutput = trim($_POST['expected_output'] ?? '');
$status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';

if ($id <= 0 || $departmentId <= 0 || $productTypeId <= 0 || $name === '' || $testingCode === '') {
    set_flash('error', 'Department, product type, name, and testing code are required.');
    redirect('admin/testing-types/index.php');
}

try {
    $dupe = $pdo->prepare(
        'SELECT id FROM testing_types WHERE department_id = :department_id AND testing_code = :testing_code AND id <> :id LIMIT 1'
    );
    $dupe->execute(['department_id' => $departmentId, 'testing_code' => $testingCode, 'id' => $id]);
    if ($dupe->fetchColumn()) {
        set_flash('error', 'Testing code already exists in this department.');
        redirect('admin/testing-types/edit.php?id=' . $id);
    }

    // Update submitted fields and leave existing test records linked to this definition.
    $stmt = $pdo->prepare(
        'UPDATE testing_types
         SET department_id = :department_id, product_type_id = :product_type_id, name = :name,
             testing_code = :testing_code, description = :description, expected_output = :expected_output,
             criteria = :criteria, status = :status
         WHERE id = :id'
    );
    $stmt->execute([
        'department_id' => $departmentId,
        'product_type_id' => $productTypeId,
        'name' => $name,
        'testing_code' => $testingCode,
        'description' => $description,
        'expected_output' => $expectedOutput,
        'criteria' => $criteria,
        'status' => $status,
        'id' => $id,
    ]);
    logActivity($pdo, current_user_id(), 'testing_type_updated', 'Updated testing type ' . $testingCode . '.');
    set_flash('success', 'Testing type updated successfully.');
} catch (Throwable $exception) {
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to update testing type.');
}

redirect('admin/testing-types/index.php');
