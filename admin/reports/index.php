<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$pdo = getPDO();
$pageTitle = 'Reports';
$activePage = 'reports';
$latest = $pdo->query(
    'SELECT tr.id, tr.test_id, tr.result, tr.status, tr.test_date, p.product_id, p.product_name
     FROM testing_records tr
     INNER JOIN products p ON p.id = tr.product_id_ref
     ORDER BY tr.created_at DESC
     LIMIT 6'
)->fetchAll();
include __DIR__ . '/../../includes/admin_start.php';
?>
<div class="page-header" data-animate="fade-up">
    <div>
        <p class="eyebrow">Reporting</p>
        <h2>Reports</h2>
        <p>Open product summaries, test summaries, or printable lab reports.</p>
    </div>
</div>

<section class="dashboard-grid">
    <div class="panel surface-card" data-animate="fade-up">
        <div class="panel-header">
            <h3>Report Types</h3>
        </div>
        <div class="quick-actions">
            <a class="quick-action" href="<?= url('admin/reports/product-report.php') ?>">
                <div><strong>Product Report</strong><span>Product status and manufacturing overview</span></div>
                <i data-lucide="package"></i>
            </a>
            <a class="quick-action" href="<?= url('admin/reports/test-report.php') ?>">
                <div><strong>Test Report</strong><span>Testing records with print actions</span></div>
                <i data-lucide="clipboard-check"></i>
            </a>
            <a class="quick-action" href="<?= url('admin/search/advanced-search.php') ?>">
                <div><strong>Advanced Search</strong><span>Build a filtered report set</span></div>
                <i data-lucide="search"></i>
            </a>
        </div>
    </div>

    <aside class="panel surface-card" data-animate="fade-up">
        <div class="panel-header">
            <h3>Latest Printable Tests</h3>
        </div>
        <div class="activity-list">
            <?php foreach ($latest as $row): ?>
                <a class="activity-item" href="<?= url('admin/reports/print-report.php?id=' . (int) $row['id']) ?>">
                    <strong><?= e($row['test_id']) ?> · <?= e($row['product_id']) ?></strong>
                    <span><?= e($row['product_name']) ?> · <?= e(formatDate($row['test_date'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </aside>
</section>
<?php include __DIR__ . '/../../includes/admin_end.php'; ?>
