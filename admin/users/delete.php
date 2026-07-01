<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin']);

if (!is_post()) {
    redirect('admin/users/index.php');
}

require_csrf();
$pdo = getPDO();
$id = (int) ($_POST['id'] ?? 0);

if ($id === current_user_id()) {
    set_flash('error', 'You cannot delete your own active session account.');
    redirect('admin/users/index.php');
}

try {
    // User deletion keeps testing records by setting created_by to NULL through the database constraint.
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute(['id' => $id]);
    logActivity($pdo, current_user_id(), 'user_deleted', 'Deleted user #' . $id . '.');
    set_flash('success', 'User deleted successfully.');
} catch (Throwable $exception) {
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to delete user.');
}

redirect('admin/users/index.php');
