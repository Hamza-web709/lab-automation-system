<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$record = fetchTestRecordDetails($pdo, $id);
if (!$record) {
    set_flash('error', 'Testing record not found.');
    redirect('admin/tests/index.php');
}
$persons = fetchTestPersons($pdo, $id);
$products = $pdo->query('SELECT id, product_id, product_name FROM products ORDER BY created_at DESC')->fetchAll();
$departments = $pdo->query('SELECT id, name FROM testing_departments ORDER BY name')->fetchAll();
$testingTypes = $pdo->query(
    'SELECT tt.*, td.name AS department_name, pt.name AS product_type_name
     FROM testing_types tt
     INNER JOIN testing_departments td ON td.id = tt.department_id
     INNER JOIN product_types pt ON pt.id = tt.product_type_id
     ORDER BY tt.name'
)->fetchAll();
$pageTitle = 'Edit Testing Record';
$activePage = 'tests';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Testing workflow</p>
        <h2>Edit Testing Record</h2>
        <p>Test ID <?= e($record['test_id']) ?> remains fixed for audit history.</p>
    </div>
</div>
<form class="form-card surface-card" method="post" action="<?= url('admin/tests/update.php') ?>" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
    <div class="form-grid">
        <div class="field">
            <label>Test ID</label>
            <input type="text" value="<?= e($record['test_id']) ?>" disabled>
        </div>
        <div class="field">
            <label>Product <span class="required">*</span></label>
            <select name="product_id_ref" required>
                <?php foreach ($products as $product): ?>
                    <option value="<?= (int) $product['id'] ?>" <?= optionSelected($record['product_id_ref'], $product['id']) ?>><?= e($product['product_id']) ?> - <?= e($product['product_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Testing Department <span class="required">*</span></label>
            <select name="department_id" required>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= (int) $department['id'] ?>" <?= optionSelected($record['department_id'], $department['id']) ?>><?= e($department['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Testing Type <span class="required">*</span></label>
            <select name="testing_type_id" required data-testing-type>
                <?php foreach ($testingTypes as $type): ?>
                    <option value="<?= (int) $type['id'] ?>" <?= optionSelected($record['testing_type_id'], $type['id']) ?> data-criteria="<?= e($type['criteria']) ?>" data-expected="<?= e($type['expected_output']) ?>">
                        <?= e($type['name']) ?> - <?= e($type['department_name']) ?> / <?= e($type['product_type_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Test Roll No <span class="required">*</span></label>
            <input type="text" name="test_roll_no" value="<?= e($record['test_roll_no']) ?>" required maxlength="20">
        </div>
        <div class="field">
            <label>Test Date <span class="required">*</span></label>
            <input type="date" name="test_date" value="<?= e($record['test_date']) ?>" required>
        </div>
        <div class="field">
            <label>Result</label>
            <select name="result">
                <option value="pending" <?= optionSelected($record['result'], 'pending') ?>>Pending</option>
                <option value="pass" <?= optionSelected($record['result'], 'pass') ?>>Pass</option>
                <option value="fail" <?= optionSelected($record['result'], 'fail') ?>>Fail</option>
            </select>
        </div>
        <div class="field">
            <label>Status</label>
            <select name="status">
                <?php foreach (['pending', 'in_progress', 'completed', 'sent_to_next_department', 'sent_to_cpri', 'sent_for_remaking'] as $item): ?>
                    <option value="<?= e($item) ?>" <?= optionSelected($record['status'], $item) ?>><?= e(humanize($item)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Next Action</label>
            <select name="next_action">
                <?php foreach (['none', 'next_test', 'send_to_cpri', 'send_for_remaking'] as $item): ?>
                    <option value="<?= e($item) ?>" <?= optionSelected($record['next_action'], $item) ?>><?= e(humanize($item)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field span-2">
            <label>Criteria</label>
            <textarea name="criteria" data-criteria><?= e($record['criteria']) ?></textarea>
        </div>
        <div class="field span-2">
            <label>Expected Output</label>
            <textarea name="expected_output" data-expected-output><?= e($record['expected_output']) ?></textarea>
        </div>
        <div class="field span-2">
            <label>Observed Output</label>
            <textarea name="observed_output"><?= e($record['observed_output']) ?></textarea>
        </div>
        <div class="field span-2">
            <label>Detailed Remarks</label>
            <textarea name="detailed_remarks"><?= e($record['detailed_remarks']) ?></textarea>
        </div>

        <div class="section-divider">Tester Persons</div>
        <div class="span-2 tester-list" data-tester-list>
            <?php $rows = $persons ?: [['person_name' => '', 'designation' => '', 'remarks' => '']]; ?>
            <?php foreach ($rows as $person): ?>
                <div class="tester-row">
                    <div class="field">
                        <label>Person Name</label>
                        <input type="text" name="person_name[]" value="<?= e($person['person_name']) ?>">
                    </div>
                    <div class="field">
                        <label>Designation</label>
                        <input type="text" name="designation[]" value="<?= e($person['designation']) ?>">
                    </div>
                    <div class="field">
                        <label>Remarks</label>
                        <input type="text" name="person_remarks[]" value="<?= e($person['remarks']) ?>">
                    </div>
                    <button class="icon-btn" type="button" aria-label="Remove tester" data-remove-tester><i data-lucide="trash-2"></i></button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-secondary" type="button" data-add-tester><i data-lucide="user-plus"></i> Add Another Tester</button>
        <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Update Test Record</button>
        <a class="btn btn-soft" href="<?= url('admin/tests/index.php') ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</form>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
