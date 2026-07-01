<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin']);

if (!is_post()) {
    redirect('admin/products/index.php');
}

require_csrf();
$pdo = getPDO();
$id = (int) ($_POST['id'] ?? 0);

try {
    // Deleting a product cascades its testing records and tester persons through database constraints.
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute(['id' => $id]);
    logActivity($pdo, current_user_id(), 'product_deleted', 'Deleted product #' . $id . '.');
    set_flash('success', 'Product deleted successfully.');
} catch (Throwable $exception) {
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to delete product.');
}

redirect('admin/products/index.php');
