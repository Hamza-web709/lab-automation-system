<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin']);

if (!is_post()) {
    redirect('admin/testing-departments/index.php');
}

require_csrf();
$pdo = getPDO();
$id = (int) ($_POST['id'] ?? 0);

try {
    // Referenced departments remain protected by foreign key constraints.
    $stmt = $pdo->prepare('DELETE FROM testing_departments WHERE id = :id');
    $stmt->execute(['id' => $id]);
    logActivity($pdo, current_user_id(), 'department_deleted', 'Deleted testing department #' . $id . '.');
    set_flash('success', 'Testing department deleted successfully.');
} catch (Throwable $exception) {
    set_flash('error', 'This department is in use and cannot be deleted.');
}

redirect('admin/testing-departments/index.php');
