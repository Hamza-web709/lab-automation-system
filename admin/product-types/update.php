<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin', 'lab_manager']);

if (!is_post()) {
    redirect('admin/product-types/index.php');
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
    redirect('admin/product-types/index.php');
}

try {
    if (valueExists($pdo, 'product_types', 'code', $code, $id)) {
        set_flash('error', 'Product type code already exists.');
        redirect('admin/product-types/edit.php?id=' . $id);
    }

    // Update keeps the original row and only changes submitted fields.
    $stmt = $pdo->prepare(
        'UPDATE product_types SET name = :name, code = :code, description = :description, status = :status WHERE id = :id'
    );
    $stmt->execute(compact('name', 'code', 'description', 'status', 'id'));
    logActivity($pdo, current_user_id(), 'product_type_updated', 'Updated product type ' . $code . '.');
    set_flash('success', 'Product type updated successfully.');
} catch (Throwable $exception) {
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to update product type.');
}

redirect('admin/product-types/index.php');
