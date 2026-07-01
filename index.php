<?php
$pageTitle = 'Smart Lab Automation System';
$bodyClass = 'landing-body';
require_once __DIR__ . '/includes/header.php';
?>
<div class="page landing-page">
    <div class="scroll-progress" aria-hidden="true"></div>
    <div class="landing-cursor landing-cursor-ring" aria-hidden="true"></div>
    <div class="landing-cursor landing-cursor-dot" aria-hidden="true"></div>

    <nav class="landing-nav">
        <div class="container landing-nav-inner">
            <a class="brand" href="<?= url() ?>">
                <span class="brand-mark"><i data-lucide="zap"></i></span>
                <span>
                    <strong>LabFlow</strong>
                    <small>Automation</small>
                </span>
            </a>
            <div class="landing-links">
                <a href="#problem">Problems</a>
                <a href="#features">Features</a>
                <a href="#workflow">Workflow</a>
                <a class="btn btn-primary" href="<?= url('auth/login.php') ?>" data-magnetic><i data-lucide="log-in"></i> Login Dashboard</a>
            </div>
        </div>
    </nav>

    <header class="landing-hero container">
        <div class="hero-copy" data-animate="fade-up">
            <p class="eyebrow hero-pill"><i data-lucide="shield-check"></i> Electrical appliance testing workflow</p>
            <h1>Smart Lab Automation System</h1>
            <p>Automated product testing records, unique product and test IDs, real-time status tracking, detailed remarks, tester attribution, and printable reports for internal lab approval workflows.</p>
            <div class="hero-actions">
                <a class="btn btn-primary btn-warm" href="<?= url('auth/login.php') ?>" data-magnetic><i data-lucide="layout-dashboard"></i> Login Dashboard</a>
                <a class="btn btn-secondary" href="#features" data-magnetic><i data-lucide="sparkles"></i> View Features</a>
            </div>
            <div class="hero-proof" data-animate="fade-up">
                <div>
                    <strong data-count="99">0</strong><span>% traceable</span>
                </div>
                <div>
                    <strong data-count="12">0</strong><span>digit test IDs</span>
                </div>
                <div>
                    <strong data-count="4">0</strong><span>lab stages</span>
                </div>
            </div>
        </div>
        <div class="hero-visual" aria-hidden="true">
            <div class="lab-console">
                <div class="console-topline">
                    <span></span><span></span><span></span>
                </div>
                <div class="hero-lottie" data-lottie="<?= asset('lottie/lab-hero.json') ?>"></div>
                <div class="signal-board">
                    <div class="signal-row is-green"><span></span><strong>Internal test passed</strong></div>
                    <div class="signal-row is-blue"><span></span><strong>Ready for CPRI</strong></div>
                    <div class="signal-row is-amber"><span></span><strong>Review pending</strong></div>
                </div>
                <div class="console-bars">
                    <span style="--h: 42%"></span>
                    <span style="--h: 72%"></span>
                    <span style="--h: 56%"></span>
                    <span style="--h: 88%"></span>
                    <span style="--h: 64%"></span>
                </div>
            </div>
            <div class="floating-metric metric-one" data-animate="fade-up" data-tilt>
                <strong>10-digit</strong>
                <span>Product trace codes</span>
            </div>
            <div class="floating-metric metric-two" data-animate="fade-up" data-tilt>
                <strong>12-digit</strong>
                <span>Test record IDs</span>
            </div>
        </div>
    </header>

    <section class="landing-section" id="problem">
        <div class="container">
            <div class="section-title" data-scroll-reveal>
                <p class="eyebrow">Paper workflow bottlenecks</p>
                <h2>Manual testing records slow down every approval.</h2>
                <p>Physical registers are easy to misplace, hard to search, and vulnerable to repeated entry mistakes during product movement between manufacturing, lab testing, CPRI approval, and re-making.</p>
            </div>
            <div class="problem-grid">
                <?php
                $problems = [
                    ['file-search', 'Misplaced Records', 'Centralized digital records keep product and testing history available.'],
                    ['circle-alert', 'Wrong Entries', 'Structured forms reduce incomplete or inconsistent test details.'],
                    ['timer', 'Slow Tracking', 'Status badges reveal where each product is in the workflow.'],
                    ['clipboard-x', 'Report Delays', 'Printable test reports are generated directly from saved data.'],
                ];
                foreach ($problems as $problem):
                ?>
                    <article class="problem-card" data-scroll-reveal data-tilt>
                        <i data-lucide="<?= e($problem[0]) ?>"></i>
                        <h3><?= e($problem[1]) ?></h3>
                        <p><?= e($problem[2]) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="landing-section solution-band">
        <div class="container">
            <div class="solution-showcase" data-scroll-reveal>
                <div>
                    <p class="eyebrow">Automation layer</p>
                    <h2>One connected workflow from manufacturing to final approval.</h2>
                    <p>The system links product families, departments, test modules, tester persons, observations, remarks, pass/fail decisions, and next actions into a single searchable testing record.</p>
                </div>
                <div class="process-mini">
                    <span>Manufactured</span>
                    <i data-lucide="arrow-right"></i>
                    <span>Internal Test</span>
                    <i data-lucide="arrow-right"></i>
                    <span>CPRI / Re-making</span>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section" id="features">
        <div class="container">
            <div class="section-title" data-scroll-reveal>
                <p class="eyebrow">Built for lab teams</p>
                <h2>Everything needed to manage internal testing records.</h2>
            </div>
            <div class="feature-grid">
                <?php
                $features = [
                    ['package', 'Product Management', 'Register products with generated product IDs and batch details.'],
                    ['flask-conical', 'Testing Modules', 'Map test types to product categories and departments.'],
                    ['binary', 'Auto Test ID', 'Generate safe unique test IDs from product and test codes.'],
                    ['search', 'Advanced Search', 'Filter records by product, test, person, result, status, and dates.'],
                    ['message-square-text', 'Detailed Remarks', 'Capture criteria, expected output, observations, and notes.'],
                    ['users', 'Tester Persons', 'Attach multiple testers or inspectors to every record.'],
                    ['printer', 'Reports', 'Open and print formatted product and testing reports.'],
                    ['bar-chart-3', 'Dashboard Analytics', 'Track totals, pass/fail counts, CPRI, and re-making status.'],
                ];
                foreach ($features as $feature):
                ?>
                    <article class="feature-card" data-scroll-reveal data-tilt>
                        <i data-lucide="<?= e($feature[0]) ?>"></i>
                        <h3><?= e($feature[1]) ?></h3>
                        <p><?= e($feature[2]) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="landing-section" id="workflow">
        <div class="container">
            <div class="section-title" data-scroll-reveal>
                <p class="eyebrow">Approval path</p>
                <h2>Clear movement for pass and fail outcomes.</h2>
            </div>
            <div class="workflow-grid">
                <?php
                $steps = [
                    ['factory', 'Manufactured Product', 'Production creates a product ready for internal lab testing.'],
                    ['activity', 'Internal Testing', 'Departments perform configured tests and record observations.'],
                    ['check-circle-2', 'Pass', 'Successful internal results can move the product toward CPRI.'],
                    ['refresh-cw', 'Fail', 'Failed products are marked for re-making or re-manufacturing.'],
                    ['badge-check', 'Final Approval', 'Approved products retain complete traceable test history.'],
                ];
                foreach ($steps as $step):
                ?>
                    <article class="workflow-step" data-scroll-reveal data-tilt>
                        <i data-lucide="<?= e($step[0]) ?>"></i>
                        <h3><?= e($step[1]) ?></h3>
                        <p><?= e($step[2]) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <strong>LabFlow Automation</strong>
            <span>Core PHP, MySQL, GSAP, and Lottie powered lab workflow system.</span>
            <a class="btn btn-primary btn-warm" href="<?= url('auth/login.php') ?>" data-magnetic><i data-lucide="log-in"></i> Open Dashboard</a>
        </div>
    </footer>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
