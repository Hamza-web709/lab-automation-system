<?php
require_once __DIR__ . '/../includes/role_check.php';

if (current_user()) {
    redirect(dashboardPathForRole(current_user()['role'] ?? null));
}

if (is_post()) {
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        set_flash('error', 'Security token mismatch. Please try again.');
        redirect('auth/login.php');
    }

    $loginIdentifier = trim($_POST['login_identifier'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    try {
        // Authentication accepts either email or username, then verifies the stored password hash.
        $pdo = getPDO();
        $stmt = $pdo->prepare(
            'SELECT * FROM users
             WHERE (email = :login_email OR username = :login_username)
             LIMIT 1'
        );
        $stmt->execute([
            'login_email' => $loginIdentifier,
            'login_username' => $loginIdentifier,
        ]);
        $user = $stmt->fetch();

        if ($user && ($user['status'] ?? '') !== 'active') {
            set_flash('error', 'Your account is inactive. Please contact administrator.');
            redirect('auth/login.php');
        }

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_status'] = $user['status'];
            logActivity($pdo, (int) $user['id'], 'login', 'User signed in.');
            redirect(dashboardPathForRole($user['role']));
        }

        set_flash('error', 'Invalid email/username or password.');
    } catch (Throwable $exception) {
        set_flash('error', APP_DEBUG ? $exception->getMessage() : 'Unable to sign in right now.');
    }

    redirect('auth/login.php');
}

$pageTitle = 'Login';
$bodyClass = 'auth-body';
include __DIR__ . '/../includes/header.php';
?>
<main class="auth-page">
    <section class="auth-copy" data-animate="fade-up">
        <a class="auth-brand brand" href="<?= url() ?>">
            <span class="brand-mark"><i data-lucide="zap"></i></span>
            <span><strong>LabFlow</strong><small>Automation</small></span>
        </a>
        <h1>Trace every product test from lab intake to approval.</h1>
        <p>Sign in to manage products, departments, testing records, reports, and workflow decisions with secure role-based access.</p>
        <div class="auth-highlights">
            <div class="auth-highlight"><strong>PDO</strong><span>Prepared queries</span></div>
            <div class="auth-highlight"><strong>CSRF</strong><span>Session tokens</span></div>
            <div class="auth-highlight"><strong>Reports</strong><span>Printable output</span></div>
        </div>
    </section>

    <section class="auth-panel surface-card" data-animate="fade-up">
        <?php include __DIR__ . '/../includes/flash.php'; ?>
        <h2>Welcome Back</h2>
        <p>Login with your email or username to access your Lab Automation panel.</p>
        <form method="post" data-validate>
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="field span-2">
                    <label>Email or Username <span class="required">*</span></label>
                    <input type="text" name="login_identifier" value="<?= e($_POST['login_identifier'] ?? 'admin') ?>" required autocomplete="username" placeholder="admin or admin@labautomation.test">
                    <small class="field-error">Email or username is required.</small>
                </div>
                <div class="field span-2">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="password" required autocomplete="current-password">
                    <small class="field-hint">Defaults: admin123, manager123, tester123.</small>
                    <small class="field-error">Password is required.</small>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><i data-lucide="log-in"></i> Sign In</button>
                <a class="btn btn-soft" href="<?= url('auth/register.php') ?>"><i data-lucide="user-plus"></i> Create Admin</a>
            </div>
        </form>
    </section>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
