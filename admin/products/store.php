<?php
require_once __DIR__ . '/../../includes/auth_check.php';

if (!is_post()) {
    redirect('admin/products/index.php');
}

require_csrf();
$pdo = getPDO();
$productTypeId = (int) ($_POST['product_type_id'] ?? 0);
$productName = trim($_POST['product_name'] ?? '');
$productCode = normalizeCodePart($_POST['product_code'] ?? '', 10);
$reviseNo = trim($_POST['revise_no'] ?? '');
$manufacturingNo = trim($_POST['manufacturing_no'] ?? '');
$batchNo = trim($_POST['batch_no'] ?? '');
$manufacturingDate = $_POST['manufacturing_date'] !== '' ? $_POST['manufacturing_date'] : null;
$description = trim($_POST['description'] ?? '');

if ($productTypeId <= 0 || $productName === '' || $productCode === '' || $reviseNo === '' || $manufacturingNo === '') {
    set_flash('error', 'Product type, name, code, revision, and manufacturing number are required.');
    redirect('admin/products/create.php');
}

try {
    $typeStmt = $pdo->prepare('SELECT code FROM product_types WHERE id = :id LIMIT 1');
    $typeStmt->execute(['id' => $productTypeId]);
    $typeCode = $typeStmt->fetchColumn();
    if (!$typeCode) {
        set_flash('error', 'Selected product type does not exist.');
        redirect('admin/products/create.php');
    }

    $productId = generateProductId($pdo, (string) $typeCode, $reviseNo, $manufacturingNo);

    // Product insert stores the generated trace ID and the original manufacturing data.
    $stmt = $pdo->prepare(
        'INSERT INTO products
        (product_id, product_type_id, product_name, product_code, revise_no, manufacturing_no, batch_no, manufacturing_date, description, current_status)
        VALUES (:product_id, :product_type_id, :product_name, :product_code, :revise_no, :manufacturing_no, :batch_no, :manufacturing_date, :description, "manufactured")'
    );
    $stmt->execute([
        'product_id' => $productId,
        'product_type_id' => $productTypeId,
        'product_name' => $productName,
        'product_code' => $productCode,
        'revise_no' => $reviseNo,
        'manufacturing_no' => $manufacturingNo,
        'batch_no' => $batchNo,
        'manufacturing_date' => $manufacturingDate,
        'description' => $description,
    ]);
    logActivity($pdo, current_user_id(), 'product_created', 'Created product ' . $productId . '.');
    set_flash('success', 'Product created with ID ' . $productId . '.');
} catch (Throwable $exception) {
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to create product.');
}

redirect('admin/products/index.php');
