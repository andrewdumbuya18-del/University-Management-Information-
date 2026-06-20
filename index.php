<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

try {
    $user = current_user();
    if ($user) {
        redirect(role_home($user['role']));
    }
} catch (Throwable $exception) {
    // Keep the public landing page available while the database is being configured.
}

$page_title = app_config('short_name') . ' | Academic life, connected';
$roles = [
    ['number' => '01', 'title' => 'Students', 'copy' => 'Modules, grades, attendance and clearance progress in one personal academic view.'],
    ['number' => '02', 'title' => 'Lecturers', 'copy' => 'A focused workspace for assigned classes, academic records and student progress.'],
    ['number' => '03', 'title' => 'Finance', 'copy' => 'Review student accounts and keep clearance decisions accurate and traceable.'],
    ['number' => '04', 'title' => 'Administrators', 'copy' => 'Coordinate users, classes, semesters and institutional activity from one command centre.'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A unified school management information system for students, lecturers, finance teams and administrators.">
    <title><?= e($page_title) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="landing-page">
    <nav class="navbar landing-nav">
        <div class="navbar-container">
            <a href="<?= url('/') ?>" class="brand-link" aria-label="<?= e(app_config('short_name')) ?> home">
                <span class="brand-text"><?= e(app_config('short_name')) ?></span>
            </a>

            <div class="landing-nav-links">
                <a href="#platform">Platform</a>
                <a href="#roles">For everyone</a>
                <a href="#workflow">How it works</a>
            </div>

            <a href="<?= url('login.php') ?>" class="btn btn-primary">Open portal <span aria-hidden="true">→</span></a>
        </div>
    </nav>

    <main>
        <section class="landing-hero">
            <div class="landing-shell hero-layout">
                <div class="hero-copy">
                    <div class="eyebrow">Freetown Technical University · Digital Campus</div>
                    <h1>One campus.<br><em>One clear view.</em></h1>
                    <p class="hero-lead">
                        A connected academic workspace that brings people, records and everyday university operations together.
                    </p>
                    <div class="hero-actions">
                        <a href="<?= url('login.php') ?>" class="btn btn-lg btn-primary">Enter your workspace <span aria-hidden="true">→</span></a>
                        <a href="#platform" class="landing-text-link">Explore the platform <span aria-hidden="true">↓</span></a>
                    </div>
                    <div class="hero-trust">
                        <span>Built for</span>
                        <strong>Students</strong>
                        <strong>Faculty</strong>
                        <strong>Operations</strong>
                    </div>
                </div>

                <div class="hero-visual" aria-label="SMIS platform overview">
                    <div class="hero-orbit orbit-one"></div>
                    <div class="hero-orbit orbit-two"></div>
                    <div class="hero-dashboard-card">
                        <div class="mini-window-bar">
                            <span></span><span></span><span></span>
                            <small>Academic overview</small>
                        </div>
                        <div class="mini-welcome">
                            <div>
                                <small>Good morning</small>
                                <strong>Your semester at a glance</strong>
                            </div>
                            <div class="mini-avatar">FT</div>
                        </div>
                        <div class="mini-stats">
                            <div><strong>06</strong><span>Modules</span></div>
                            <div><strong>92%</strong><span>Attendance</span></div>
                            <div><strong>04</strong><span>Clearances</span></div>
                        </div>
                        <div class="mini-progress">
                            <div class="mini-progress-heading"><span>Semester progress</span><strong>68%</strong></div>
                            <div class="progress-track"><span></span></div>
                        </div>
                        <div class="mini-list">
                            <div><span class="mini-icon coral">M</span><p><strong>Management Information Systems</strong><small>Next class · Monday</small></p><b>→</b></div>
                            <div><span class="mini-icon mint">D</span><p><strong>Database Systems</strong><small>Grade published</small></p><b>→</b></div>
                        </div>
                    </div>
                    <div class="floating-note note-top"><span>●</span> Records in sync</div>
                    <div class="floating-note note-bottom"><strong>A</strong><span>Academic clarity<br><small>at every step</small></span></div>
                </div>
            </div>
        </section>

        <section class="landing-proof">
            <div class="landing-shell proof-grid">
                <div><strong>01</strong><span>Reliable source<br>of academic truth</span></div>
                <div><strong>02</strong><span>Role-based and<br>secure by design</span></div>
                <div><strong>03</strong><span>Less paperwork,<br>faster decisions</span></div>
                <div><strong>04</strong><span>Built around real<br>campus workflows</span></div>
            </div>
        </section>

        <section class="landing-section platform-section" id="platform">
            <div class="landing-shell">
                <div class="section-heading">
                    <div>
                        <span class="section-kicker">The platform</span>
                        <h2>Everything academic life needs,<br><em>without the clutter.</em></h2>
                    </div>
                    <p>SMIS turns scattered information into one dependable workflow—from registration through final clearance.</p>
                </div>

                <div class="feature-editorial-grid">
                    <article class="feature-large">
                        <span class="feature-index">01 / Academic records</span>
                        <h3>See the full student journey.</h3>
                        <p>Bring modules, attendance, grades and progression into one coherent record that is easy to understand and act on.</p>
                        <div class="record-preview">
                            <div class="record-person"><span>JK</span><p><strong>John Kamara</strong><small>BIT 2 · Semester 1</small></p><b>Active</b></div>
                            <div class="record-lines"><i style="width:92%"></i><i style="width:74%"></i><i style="width:84%"></i></div>
                        </div>
                    </article>
                    <article class="feature-small feature-dark">
                        <span class="feature-index">02 / Clearances</span>
                        <h3>Move approvals forward.</h3>
                        <p>Track every decision with visible status, remarks and accountability.</p>
                        <div class="clearance-ring"><span>3<small>/4</small></span></div>
                    </article>
                    <article class="feature-small feature-warm">
                        <span class="feature-index">03 / Insight</span>
                        <h3>Know what needs attention.</h3>
                        <p>Purposeful dashboards surface activity, performance and pending work.</p>
                        <div class="insight-bars"><i></i><i></i><i></i><i></i><i></i></div>
                    </article>
                </div>
            </div>
        </section>

        <section class="landing-section roles-section" id="roles">
            <div class="landing-shell">
                <div class="section-heading section-heading-light">
                    <div>
                        <span class="section-kicker">Designed around people</span>
                        <h2>A focused experience<br><em>for every role.</em></h2>
                    </div>
                    <p>Everyone sees what matters to them, while the institution stays connected behind the scenes.</p>
                </div>

                <div class="role-list">
                    <?php foreach ($roles as $role): ?>
                        <article>
                            <span><?= e($role['number']) ?></span>
                            <h3><?= e($role['title']) ?></h3>
                            <p><?= e($role['copy']) ?></p>
                            <b aria-hidden="true">↗</b>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="landing-section workflow-section" id="workflow">
            <div class="landing-shell workflow-layout">
                <div class="workflow-intro">
                    <span class="section-kicker">Simple by design</span>
                    <h2>From sign-in to insight in three clear steps.</h2>
                    <p>No maze of menus. No disconnected records. Just the right workspace for the work in front of you.</p>
                    <a href="<?= url('login.php') ?>" class="landing-text-link">Access the portal <span>→</span></a>
                </div>
                <ol class="workflow-steps">
                    <li><span>01</span><div><h3>Sign in securely</h3><p>Use your institutional account to enter the workspace assigned to your role.</p></div></li>
                    <li><span>02</span><div><h3>See what matters</h3><p>Your dashboard brings current records, actions and updates into immediate view.</p></div></li>
                    <li><span>03</span><div><h3>Move work forward</h3><p>Complete academic and operational tasks with a clear, traceable flow.</p></div></li>
                </ol>
            </div>
        </section>

        <section class="landing-cta">
            <div class="landing-shell cta-card">
                <div>
                    <span class="section-kicker">Your campus, connected</span>
                    <h2>Ready to get to work?</h2>
                    <p>Open your SMIS workspace and pick up exactly where you left off.</p>
                </div>
                <a href="<?= url('login.php') ?>" class="btn btn-lg btn-primary">Sign in to SMIS <span aria-hidden="true">→</span></a>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
