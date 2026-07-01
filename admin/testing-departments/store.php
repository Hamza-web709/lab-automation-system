<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin', 'lab_manager']);

if (!is_post()) {
    redirect('admin/testing-departments/index.php');
}

require_csrf();
$pdo = getPDO();
$name = trim($_POST['name'] ?? '');
$code = normalizeCodePart($_POST['code'] ?? '', 10);
$description = trim($_POST['description'] ?? '');
$status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';

if ($name === '' || $code === '') {
    set_flash('error', 'Name and code are required.');
    redirect('admin/testing-departments/create.php');
}

try {
    if (valueExists($pdo, 'testing_departments', 'code', $code)) {
        set_flash('error', 'Department code already exists.');
        redirect('admin/testing-departments/create.php');
    }

    // Insert operation is parameterized to avoid trusting form input.
    $stmt = $pdo->prepare(
        'INSERT INTO testing_departments (name, code, description, status) VALUES (:name, :code, :description, :status)'
    );
    $stmt->execute(compact('name', 'code', 'description', 'status'));
    logActivity($pdo, current_user_id(), 'department_created', 'Created testing department ' . $code . '.');
    set_flash('success', 'Testing department created successfully.');
} catch (Throwable $exception) {
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to create department.');
}

redirect('admin/testing-departments/index.php');
