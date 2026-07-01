<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin', 'lab_manager']);

if (!is_post()) {
    redirect('admin/testing-departments/index.php');
}

require_csrf();
$pdo = getPDO();
$id = (int) ($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$code = normalizeCodePart($_POST['code'] ?? '', 10);
$description = trim($_POST['description'] ?? '');
$status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';

if ($id <= 0 || $name === '' || $code === '') {
    set_flash('error', 'Name and code are required.');
    redirect('admin/testing-departments/index.php');
}

try {
    if (valueExists($pdo, 'testing_departments', 'code', $code, $id)) {
        set_flash('error', 'Department code already exists.');
        redirect('admin/testing-departments/edit.php?id=' . $id);
    }

    // Update operation is scoped to the selected department row.
    $stmt = $pdo->prepare(
        'UPDATE testing_departments SET name = :name, code = :code, description = :description, status = :status WHERE id = :id'
    );
    $stmt->execute(compact('name', 'code', 'description', 'status', 'id'));
    logActivity($pdo, current_user_id(), 'department_updated', 'Updated testing department ' . $code . '.');
    set_flash('success', 'Testing department updated successfully.');
} catch (Throwable $exception) {
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to update department.');
}

redirect('admin/testing-departments/index.php');
