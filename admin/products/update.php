<?php
require_once __DIR__ . '/../../includes/auth_check.php';

if (!is_post()) {
    redirect('admin/products/index.php');
}

require_csrf();
$pdo = getPDO();
$id = (int) ($_POST['id'] ?? 0);
$productTypeId = (int) ($_POST['product_type_id'] ?? 0);
$productName = trim($_POST['product_name'] ?? '');
$productCode = normalizeCodePart($_POST['product_code'] ?? '', 10);
$reviseNo = trim($_POST['revise_no'] ?? '');
$manufacturingNo = trim($_POST['manufacturing_no'] ?? '');
$batchNo = trim($_POST['batch_no'] ?? '');
$manufacturingDate = $_POST['manufacturing_date'] !== '' ? $_POST['manufacturing_date'] : null;
$description = trim($_POST['description'] ?? '');
$currentStatus = $_POST['current_status'] ?? 'manufactured';
$validStatuses = ['manufactured', 'under_testing', 'passed_internal', 'failed_internal', 'sent_to_cpri', 'approved', 'sent_for_remaking'];

if ($id <= 0 || $productTypeId <= 0 || $productName === '' || $productCode === '' || $reviseNo === '' || $manufacturingNo === '' || !in_array($currentStatus, $validStatuses, true)) {
    set_flash('error', 'Please complete the required product fields.');
    redirect('admin/products/index.php');
}

try {
    // Update keeps the generated product_id unchanged to preserve historical references.
    $stmt = $pdo->prepare(
        'UPDATE products
         SET product_type_id = :product_type_id, product_name = :product_name, product_code = :product_code,
             revise_no = :revise_no, manufacturing_no = :manufacturing_no, batch_no = :batch_no,
             manufacturing_date = :manufacturing_date, description = :description, current_status = :current_status
         WHERE id = :id'
    );
    $stmt->execute([
        'product_type_id' => $productTypeId,
        'product_name' => $productName,
        'product_code' => $productCode,
        'revise_no' => $reviseNo,
        'manufacturing_no' => $manufacturingNo,
        'batch_no' => $batchNo,
        'manufacturing_date' => $manufacturingDate,
        'description' => $description,
        'current_status' => $currentStatus,
        'id' => $id,
    ]);
    logActivity($pdo, current_user_id(), 'product_updated', 'Updated product #' . $id . '.');
    set_flash('success', 'Product updated successfully.');
} catch (Throwable $exception) {
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to update product.');
}

redirect('admin/products/index.php');
