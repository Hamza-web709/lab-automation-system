<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin', 'lab_manager']);

if (!is_post()) {
    redirect('admin/product-types/index.php');
}

require_csrf();
$pdo = getPDO();
$name = trim($_POST['name'] ?? '');
$code = normalizeCodePart($_POST['code'] ?? '', 10);
$description = trim($_POST['description'] ?? '');
$status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';

if ($name === '' || $code === '') {
    set_flash('error', 'Name and code are required.');
    redirect('admin/product-types/create.php');
}

try {
    if (valueExists($pdo, 'product_types', 'code', $code)) {
        set_flash('error', 'Product type code already exists.');
        redirect('admin/product-types/create.php');
    }

    // Insert uses prepared statements to keep master data changes safe.
    $stmt = $pdo->prepare(
        'INSERT INTO product_types (name, code, description, status) VALUES (:name, :code, :description, :status)'
    );
    $stmt->execute(compact('name', 'code', 'description', 'status'));
    logActivity($pdo, current_user_id(), 'product_type_created', 'Created product type ' . $code . '.');
    set_flash('success', 'Product type created successfully.');
} catch (Throwable $exception) {
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to create product type.');
}

redirect('admin/product-types/index.php');
