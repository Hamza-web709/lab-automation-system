<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin']);

if (!is_post()) {
    redirect('admin/testing-types/index.php');
}

require_csrf();
$pdo = getPDO();
$id = (int) ($_POST['id'] ?? 0);

try {
    // Testing types referenced by records are protected by the database.
    $stmt = $pdo->prepare('DELETE FROM testing_types WHERE id = :id');
    $stmt->execute(['id' => $id]);
    logActivity($pdo, current_user_id(), 'testing_type_deleted', 'Deleted testing type #' . $id . '.');
    set_flash('success', 'Testing type deleted successfully.');
} catch (Throwable $exception) {
    set_flash('error', 'This testing type is in use and cannot be deleted.');
}

redirect('admin/testing-types/index.php');
