<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$canRegister = false;
$userCount = 0;

try {
    $pdo = getPDO();
    $userCount = tableCount($pdo, 'users');
    $current = current_user();
    $canRegister = $userCount === 0 || ($current && $current['role'] === 'admin');
} catch (Throwable $exception) {
    $pdo = null;
    set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Database is not ready.');
}

if (!$canRegister && $pdo) {
    set_flash('warning', 'Admin registration is only available before the first account or from an admin session.');
    redirect('auth/login.php');
}

if (is_post() && $pdo) {
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        set_flash('error', 'Security token mismatch. Please try again.');
        redirect('auth/register.php');
    }

    $name = trim($_POST['name'] ?? '');
    $username = normalizeUsername($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $role = $userCount === 0 ? 'admin' : ($_POST['role'] ?? 'tester');
    $allowedRoles = ['admin', 'lab_manager', 'tester'];

    if ($name === '' || $email === '' || !isValidUsername($username) || strlen($password) < 6 || !in_array($role, $allowedRoles, true)) {
        set_flash('error', 'Please enter a valid name, username, email, role, and password of at least 6 characters. ' . usernameValidationMessage());
        redirect('auth/register.php');
    }

    try {
        if (valueExists($pdo, 'users', 'email', $email)) {
            set_flash('error', 'That email is already registered.');
            redirect('auth/register.php');
        }
        if (valueExists($pdo, 'users', 'username', $username)) {
            set_flash('error', 'That username is already registered.');
            redirect('auth/register.php');
        }

        // Passwords are stored using password_hash so future PHP upgrades can rehash safely.
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, username, email, password, role, status) VALUES (:name, :username, :email, :password, :role, "active")'
        );
        $stmt->execute([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
        logActivity($pdo, (int) $pdo->lastInsertId(), 'user_registered', 'New user account created.');
        set_flash('success', 'Account created. You can sign in now.');
        redirect('auth/login.php');
    } catch (Throwable $exception) {
        set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to create the account.');
        redirect('auth/register.php');
    }
}

$pageTitle = $userCount === 0 ? 'Create First Admin' : 'Create User';
$bodyClass = 'auth-body';
include __DIR__ . '/../includes/header.php';
?>
<main class="auth-page">
    <section class="auth-copy" data-animate="fade-up">
        <a class="auth-brand brand" href="<?= url() ?>">
            <span class="brand-mark"><i data-lucide="zap"></i></span>
            <span><strong>LabFlow</strong><small>Automation</small></span>
        </a>
        <h1><?= $userCount === 0 ? 'Create the first administrator account.' : 'Create a secure lab user account.' ?></h1>
        <p>Registration is protected so the public page cannot create additional users after initial setup unless an administrator is already signed in.</p>
    </section>

    <section class="auth-panel surface-card" data-animate="fade-up">
        <?php include __DIR__ . '/../includes/flash.php'; ?>
        <h2><?= e($pageTitle) ?></h2>
        <p><?= $userCount === 0 ? 'This account will own the first system setup.' : 'Assign the correct operational role.' ?></p>
        <form method="post" data-validate>
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="field span-2">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" required>
                    <small class="field-error">Name is required.</small>
                </div>
                <div class="field span-2">
                    <label>Username <span class="required">*</span></label>
                    <input type="text" name="username" required minlength="3" maxlength="30" pattern="[a-zA-Z0-9_.]{3,30}" placeholder="admin">
                    <small class="field-hint">Letters, numbers, underscore, and dot only. No spaces.</small>
                    <small class="field-error">Username is required.</small>
                </div>
                <div class="field span-2">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" required>
                    <small class="field-error">Email is required.</small>
                </div>
                <div class="field span-2">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="password" required minlength="6">
                    <small class="field-hint">Use at least 6 characters.</small>
                    <small class="field-error">Password is required.</small>
                </div>
                <?php if ($userCount > 0): ?>
                    <div class="field span-2">
                        <label>Role <span class="required">*</span></label>
                        <select name="role" required>
                            <option value="tester">Tester</option>
                            <option value="lab_manager">Lab Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><i data-lucide="user-plus"></i> Create Account</button>
                <a class="btn btn-soft" href="<?= url('auth/login.php') ?>"><i data-lucide="arrow-left"></i> Back to Login</a>
            </div>
        </form>
    </section>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
