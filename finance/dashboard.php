<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_role('finance');
$page_title = 'Finance Dashboard - SMIS';

// Get finance officer info
$finance = db_one('SELECT * FROM finance_officers WHERE user_id = :id', ['id' => current_user()['id']]);

if (!$finance) {
    set_flash('error', 'Finance officer profile not found.');
    redirect('login.php');
}

// Get statistics
$total_students = (int) db_value('SELECT COUNT(*) FROM students');

$cleared_students = (int) db_value(
    'SELECT COUNT(*) FROM finance_clearance WHERE status = "cleared"'
);

$pending_clearances = (int) db_value(
    'SELECT COUNT(*) FROM students s
     LEFT JOIN finance_clearance fc ON fc.student_id = s.id
     WHERE COALESCE(fc.status, "pending") = "pending"'
);

$not_cleared = (int) db_value(
    'SELECT COUNT(*) FROM finance_clearance WHERE status = "not_cleared"'
);

$clearance_percentage = $total_students > 0 ? round(($cleared_students / $total_students) * 100, 1) : 0;

?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>Welcome, <?= e(current_user()['name']) ?></h1>
    <p>Finance Officer | Staff Number: <?= e($finance['staff_number']) ?></p>
</div>

<!-- Statistics -->
<div class="grid grid-col-4">
    <div class="stat-card">
        <div class="stat-card-icon">👥</div>
        <div class="stat-card-value"><?= $total_students ?></div>
        <div class="stat-card-label">Total Students</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">✅</div>
        <div class="stat-card-value"><?= $cleared_students ?></div>
        <div class="stat-card-label">Cleared</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">⏳</div>
        <div class="stat-card-value"><?= $pending_clearances ?></div>
        <div class="stat-card-label">Pending</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">❌</div>
        <div class="stat-card-value"><?= $not_cleared ?></div>
        <div class="stat-card-label">Not Cleared</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">📊</div>
        <div class="stat-card-value"><?= $clearance_percentage ?>%</div>
        <div class="stat-card-label">Clearance Rate</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-col-2">
    <div class="card">
        <div class="card-header">Quick Actions</div>
        <div class="card-body">
            <div style="display: grid; gap: 10px;">
                <a href="<?= url('finance/clearances.php') ?>" class="btn btn-primary">Manage Clearances</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Clearance Status</div>
        <div class="card-body">
            <div style="padding: 15px 0;">
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <strong>Cleared</strong>
                        <span><?= $cleared_students ?> / <?= $total_students ?></span>
                    </div>
                    <div style="background: #eee; border-radius: 3px; height: 20px; overflow: hidden;">
                        <div style="background: #28a745; height: 100%; width: <?= $clearance_percentage ?>%; transition: width 0.3s ease;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    margin-bottom: 5px;
}

.page-header p {
    color: #666;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.stat-card-value {
    font-size: 28px;
    font-weight: 700;
    color: #007bff;
}

.stat-card-label {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
    text-transform: uppercase;
}

.stat-card-icon {
    font-size: 32px;
    margin-bottom: 10px;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
