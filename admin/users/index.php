<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin']);
$pdo = getPDO();
$pageTitle = 'Users';
$activePage = 'users';

$q = trim($_GET['q'] ?? '');
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';
$where = [];
$params = [];

if ($q !== '') {
    $searchTerm = '%' . $q . '%';
    $where[] = '(name LIKE :q_name OR username LIKE :q_username OR email LIKE :q_email)';
    $params['q_name'] = $searchTerm;
    $params['q_username'] = $searchTerm;
    $params['q_email'] = $searchTerm;
}
if (in_array($role, ['admin', 'lab_manager', 'tester'], true)) {
    $where[] = 'role = :role';
    $params['role'] = $role;
}
if (in_array($status, ['active', 'inactive'], true)) {
    $where[] = 'status = :status';
    $params['status'] = $status;
}

$sql = 'SELECT * FROM users';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Access control</p>
        <h2>Users</h2>
        <p>Manage secure accounts for administrators, lab managers, and testers.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('admin/users/create.php') ?>"><i data-lucide="user-plus"></i> Add User</a>
</div>

<form class="filter-bar surface-card" method="get" data-animate="fade-up">
    <div class="field">
        <label>Search</label>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Name, username, or email">
    </div>
    <div class="field">
        <label>Role</label>
        <select name="role">
            <option value="">All roles</option>
            <?php foreach (['admin', 'lab_manager', 'tester'] as $item): ?>
                <option value="<?= e($item) ?>" <?= optionSelected($role, $item) ?>><?= e(humanize($item)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label>Status</label>
        <select name="status">
            <option value="">All statuses</option>
            <option value="active" <?= optionSelected($status, 'active') ?>>Active</option>
            <option value="inactive" <?= optionSelected($status, 'inactive') ?>>Inactive</option>
        </select>
    </div>
    <button class="btn btn-secondary" type="submit"><i data-lucide="filter"></i> Filter</button>
</form>

<section class="surface-card table-shell" data-animate="fade-up">
    <table class="data-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><strong><?= e($user['name']) ?></strong></td>
                    <td>@<?= e($user['username']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td><?= e(humanize($user['role'])) ?></td>
                    <td><?= statusBadge($user['status']) ?></td>
                    <td><?= e(formatDate($user['created_at'])) ?></td>
                    <td>
                        <div class="table-actions">
                            <a class="icon-btn" href="<?= url('admin/users/edit.php?id=' . (int) $user['id']) ?>" aria-label="Edit"><i data-lucide="pencil"></i></a>
                            <?php if ((int) $user['id'] !== current_user_id()): ?>
                                <form method="post" action="<?= url('admin/users/delete.php') ?>" onsubmit="return confirm('Delete this user?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                    <button class="icon-btn" type="submit" aria-label="Delete"><i data-lucide="trash-2"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
