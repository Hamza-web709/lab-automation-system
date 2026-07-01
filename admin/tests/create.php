<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$selectedProductId = (int) ($_GET['product_id'] ?? 0);
$products = $pdo->query('SELECT id, product_id, product_name, product_code, revise_no FROM products ORDER BY created_at DESC')->fetchAll();
$departments = $pdo->query('SELECT id, name FROM testing_departments WHERE status = "active" ORDER BY name')->fetchAll();
$testingTypes = $pdo->query(
    'SELECT tt.*, td.name AS department_name, pt.name AS product_type_name
     FROM testing_types tt
     INNER JOIN testing_departments td ON td.id = tt.department_id
     INNER JOIN product_types pt ON pt.id = tt.product_type_id
     WHERE tt.status = "active"
     ORDER BY tt.name'
)->fetchAll();
$pageTitle = 'Add Testing Record';
$activePage = 'tests';
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Testing workflow</p>
        <h2>Add Testing Record</h2>
        <p>The Test ID is generated from product code, revision, testing code, and roll number.</p>
    </div>
</div>
<form class="form-card surface-card" method="post" action="<?= url('admin/tests/store.php') ?>" data-validate data-animate="fade-up">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="section-divider">Record Setup</div>
        <div class="field">
            <label>Product <span class="required">*</span></label>
            <select name="product_id_ref" required>
                <option value="">Select product</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= (int) $product['id'] ?>" <?= optionSelected($selectedProductId, $product['id']) ?>>
                        <?= e($product['product_id']) ?> - <?= e($product['product_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="field-error">Product is required.</small>
        </div>
        <div class="field">
            <label>Testing Department <span class="required">*</span></label>
            <select name="department_id" required>
                <option value="">Select department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= (int) $department['id'] ?>"><?= e($department['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <small class="field-error">Department is required.</small>
        </div>
        <div class="field">
            <label>Testing Type <span class="required">*</span></label>
            <select name="testing_type_id" required data-testing-type>
                <option value="">Select testing type</option>
                <?php foreach ($testingTypes as $type): ?>
                    <option value="<?= (int) $type['id'] ?>" data-criteria="<?= e($type['criteria']) ?>" data-expected="<?= e($type['expected_output']) ?>">
                        <?= e($type['name']) ?> - <?= e($type['department_name']) ?> / <?= e($type['product_type_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="field-error">Testing type is required.</small>
        </div>
        <div class="field">
            <label>Test Roll No <span class="required">*</span></label>
            <input type="text" name="test_roll_no" required maxlength="20" placeholder="001">
            <small class="field-error">Roll number is required.</small>
        </div>
        <div class="field">
            <label>Test Date <span class="required">*</span></label>
            <input type="date" name="test_date" value="<?= e(date('Y-m-d')) ?>" required>
        </div>
        <div class="field">
            <label>Result</label>
            <select name="result">
                <option value="pending">Pending</option>
                <option value="pass">Pass</option>
                <option value="fail">Fail</option>
            </select>
        </div>
        <div class="field">
            <label>Status</label>
            <select name="status">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="sent_to_next_department">Sent to Next Department</option>
                <option value="sent_to_cpri">Sent to CPRI</option>
                <option value="sent_for_remaking">Sent for Re-making</option>
            </select>
        </div>
        <div class="field">
            <label>Next Action</label>
            <select name="next_action">
                <option value="none">None</option>
                <option value="next_test">Next Test</option>
                <option value="send_to_cpri">Send to CPRI</option>
                <option value="send_for_remaking">Send for Re-making</option>
            </select>
        </div>

        <div class="section-divider">Testing Details</div>
        <div class="field span-2">
            <label>Criteria</label>
            <textarea name="criteria" data-criteria></textarea>
        </div>
        <div class="field span-2">
            <label>Expected Output</label>
            <textarea name="expected_output" data-expected-output></textarea>
        </div>
        <div class="field span-2">
            <label>Observed Output</label>
            <textarea name="observed_output"></textarea>
        </div>
        <div class="field span-2">
            <label>Detailed Remarks</label>
            <textarea name="detailed_remarks"></textarea>
        </div>

        <div class="section-divider">Tester Persons</div>
        <div class="span-2 tester-list" data-tester-list>
            <div class="tester-row">
                <div class="field">
                    <label>Person Name</label>
                    <input type="text" name="person_name[]" placeholder="Tester name">
                </div>
                <div class="field">
                    <label>Designation</label>
                    <input type="text" name="designation[]" placeholder="Engineer / Inspector">
                </div>
                <div class="field">
                    <label>Remarks</label>
                    <input type="text" name="person_remarks[]" placeholder="Optional remarks">
                </div>
                <button class="icon-btn" type="button" aria-label="Remove tester" data-remove-tester><i data-lucide="trash-2"></i></button>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-secondary" type="button" data-add-tester><i data-lucide="user-plus"></i> Add Another Tester</button>
        <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Save Test Record</button>
        <a class="btn btn-soft" href="<?= url('admin/tests/index.php') ?>"><i data-lucide="arrow-left"></i> Back</a>
    </div>
</form>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
