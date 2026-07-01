<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['admin', 'lab_manager']);
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM testing_departments WHERE id = :id');
$stmt->execute(['id' => $id]);
$department = $stmt->fetch();
if (!$department) {
    set_flash('error', 'Testing department not found.');
    redirect('admin/testing-departments/index.php');
}
$pageTitle = 'Edit Testing Department';
$activePage = 'testing-departments';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Lab structure</p>
        <h2>Edit Testing Department</h2>
        <p>Keep department names and codes aligned with lab operations.</p>
    </div>
</div>
<form class="form-card surface-card" method="post" action="<?= url('admin/testing-departments/update.php') ?>" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $department['id'] ?>">
    <div class="form-grid">
        <div class="field">
            <label>Name <span class="required">*</span></label>
            <input type="text" name="name" value="<?= e($department['name']) ?>" required maxlength="120">
            <small class="field-error">Name is required.</small>
        </div>
        <div class="field">
            <label>Code <span class="required">*</span></label>
            <input type="text" name="code" value="<?= e($department['code']) ?>" required maxlength="10">
            <small class="field-error">Code is required.</small>
        </div>
        <div class="field span-2">
            <label>Description</label>
            <textarea name="description"><?= e($department['description']) ?></textarea>
        </div>
        <div class="field">
            <label>Status</label>
            <select name="status">
                <option value="active" <?= optionSelected($department['status'], 'active') ?>>Active</option>
                <option value="inactive" <?= optionSelected($department['status'], 'inactive') ?>>Inactive</option>
            </select>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Update Department</button>
        <a class="btn btn-soft" href="<?= url('admin/testing-departments/index.php') ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</form>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
