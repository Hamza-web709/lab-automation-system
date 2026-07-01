<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin']);

if (!is_post()) {
    redirect('admin/users/index.php');
}

require_csrf();
$pdo = getPDO();
$name = trim($_POST['name'] ?? '');
$username = normalizeUsername($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = (string) ($_POST['password'] ?? '');
$role = $_POST['role'] ?? 'tester';
$status = $_POST['status'] ?? 'active';

if ($name === '' || $email === '' || !isValidUsername($username) || strlen($password) < 6 || !in_array($role, ['admin', 'lab_manager', 'tester'], true) || !in_array($status, ['active', 'inactive'], true)) {
    if ($username !== '' && !isValidUsername($username)) {
        set_flash('error', usernameValidationMessage());
    } else {
        set_flash('error', 'Please complete all required user fields.');
    }
    redirect('admin/users/create.php');
}

try {
    if (valueExists($pdo, 'users', 'username', $username)) {
        set_flash('error', 'Username already exists.');
        redirect('admin/users/create.php');
    }
    if (valueExists($pdo, 'users', 'email', $email)) {
        set_flash('error', 'Email already exists.');
        redirect('admin/users/create.php');
    }

    // User passwords are hashed before insert and never stored as plain text.
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, username, email, password, role, status) VALUES (:name, :username, :email, :password, :role, :status)'
    );
    $stmt->execute([
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $role,
        'status' => $status,
    ]);
    logActivity($pdo, current_user_id(), 'user_created', 'Created user ' . $username . ' (' . $email . ').');
    set_flash('success', 'User created successfully.');
} catch (Throwable $exception) {
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to create user.');
}

redirect('admin/users/index.php');
