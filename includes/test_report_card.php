<?php
/** @var array $record */
/** @var array $persons */
$printMode = $printMode ?? false;
?>
<article class="report-paper surface-card" data-animate="fade-up">
    <div class="report-header">
        <div>
            <p class="eyebrow">Laboratory Testing Report</p>
            <h2><?= e($record['test_id']) ?></h2>
            <p><?= e($record['testing_type_name']) ?> · <?= e($record['department_name']) ?></p>
        </div>
        <div>
            <?= resultBadge($record['result']) ?>
            <?= statusBadge($record['status']) ?>
        </div>
    </div>

    <div class="report-grid">
        <?php
        $fields = [
            'Test ID' => $record['test_id'],
            'Product ID' => $record['product_id'],
            'Product Name' => $record['product_name'],
            'Product Type' => $record['product_type_name'],
            'Revise No' => $record['revise_no'],
            'Manufacturing No' => $record['manufacturing_no'],
            'Testing Department' => $record['department_name'],
            'Testing Type' => $record['testing_type_name'],
            'Test Roll No' => $record['test_roll_no'],
            'Test Date' => formatDate($record['test_date']),
            'Next Action' => humanize($record['next_action']),
            'Assigned Tester' => $record['assigned_tester_name'] ?: 'Not assigned',
            'Created By' => $record['created_by_name'] ?: 'System',
        ];
        foreach ($fields as $label => $value):
        ?>
            <div class="report-field">
                <span><?= e($label) ?></span>
                <strong><?= e($value) ?></strong>
            </div>
        <?php endforeach; ?>

        <?php
        $longFields = [
            'Criteria' => $record['criteria'],
            'Expected Output' => $record['expected_output'],
            'Observed Output' => $record['observed_output'],
            'Detailed Remarks' => $record['detailed_remarks'],
        ];
        foreach ($longFields as $label => $value):
        ?>
            <div class="report-field" style="grid-column: 1 / -1;">
                <span><?= e($label) ?></span>
                <p><?= nl2br(e($value ?: '-')) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="panel-header">
        <h3>Tester Persons</h3>
    </div>
    <?php if ($persons): ?>
        <div class="table-shell">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Person Name</th>
                        <th>Designation</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($persons as $person): ?>
                        <tr>
                            <td><?= e($person['person_name']) ?></td>
                            <td><?= e($person['designation'] ?: '-') ?></td>
                            <td><?= e($person['remarks'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="field-hint">No tester persons were attached to this record.</p>
    <?php endif; ?>
</article>
