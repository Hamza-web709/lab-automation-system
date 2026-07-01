<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin']);

if (!is_post()) {
    redirect('admin/users/index.php');
}

require_csrf();
$pdo = getPDO();
$id = (int) ($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$username = normalizeUsername($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = (string) ($_POST['password'] ?? '');
$role = $_POST['role'] ?? 'tester';
$status = $_POST['status'] ?? 'active';

if ($id <= 0 || $name === '' || $email === '' || !isValidUsername($username) || !in_array($role, ['admin', 'lab_manager', 'tester'], true) || !in_array($status, ['active', 'inactive'], true)) {
    set_flash('error', !isValidUsername($username) ? usernameValidationMessage() : 'Please complete all required user fields.');
    redirect($id > 0 ? 'admin/users/edit.php?id=' . $id : 'admin/users/index.php');
}

try {
    if (valueExists($pdo, 'users', 'username', $username, $id)) {
        set_flash('error', 'Username already exists.');
        redirect('admin/users/edit.php?id=' . $id);
    }
    if (valueExists($pdo, 'users', 'email', $email, $id)) {
        set_flash('error', 'Email already exists.');
        redirect('admin/users/edit.php?id=' . $id);
    }

    $params = [
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'role' => $role,
        'status' => $status,
        'id' => $id,
    ];
    $passwordSql = '';

    // Password update is optional so admins can edit profile metadata without resetting credentials.
    if ($password !== '') {
        if (strlen($password) < 6) {
            set_flash('error', 'Password must be at least 6 characters.');
            redirect('admin/users/edit.php?id=' . $id);
        }
        $passwordSql = ', password = :password';
        $params['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    $stmt = $pdo->prepare(
        'UPDATE users SET name = :name, username = :username, email = :email, role = :role, status = :status' . $passwordSql . ' WHERE id = :id'
    );
    $stmt->execute($params);
    if ($id === current_user_id()) {
        $_SESSION['user_name'] = $name;
        $_SESSION['username'] = $username;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $role;
        $_SESSION['role'] = $role;
        $_SESSION['user_status'] = $status;
    }
    logActivity($pdo, current_user_id(), 'user_updated', 'Updated user ' . $username . ' (' . $email . ').');
    set_flash('success', 'User updated successfully.');
} catch (Throwable $exception) {
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to update user.');
}

redirect('admin/users/index.php');
