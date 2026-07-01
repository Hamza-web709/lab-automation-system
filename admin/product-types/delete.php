<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin']);

if (!is_post()) {
    redirect('admin/product-types/index.php');
}

require_csrf();
$pdo = getPDO();
$id = (int) ($_POST['id'] ?? 0);

try {
    // Foreign keys prevent deleting product types already used by products or test definitions.
    $stmt = $pdo->prepare('DELETE FROM product_types WHERE id = :id');
    $stmt->execute(['id' => $id]);
    logActivity($pdo, current_user_id(), 'product_type_deleted', 'Deleted product type #' . $id . '.');
    set_flash('success', 'Product type deleted successfully.');
} catch (Throwable $exception) {
    set_flash('error', 'This product type is in use and cannot be deleted.');
}

redirect('admin/product-types/index.php');
