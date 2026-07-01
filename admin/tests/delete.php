<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin', 'lab_manager']);

if (!is_post()) {
    redirect('admin/tests/index.php');
}

require_csrf();
$pdo = getPDO();
$id = (int) ($_POST['id'] ?? 0);

try {
    // Tester persons are removed automatically through ON DELETE CASCADE.
    $stmt = $pdo->prepare('DELETE FROM testing_records WHERE id = :id');
    $stmt->execute(['id' => $id]);
    logActivity($pdo, current_user_id(), 'testing_record_deleted', 'Deleted testing record #' . $id . '.');
    set_flash('success', 'Testing record deleted successfully.');
} catch (Throwable $exception) {
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to delete testing record.');
}

redirect('admin/tests/index.php');
