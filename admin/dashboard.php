<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

require_role('admin');
$page_title = 'Admin Dashboard - SMIS';

// Get dashboard statistics
$total_users = (int) db_value('SELECT COUNT(*) FROM users');
$total_students = (int) db_value('SELECT COUNT(*) FROM students');
$total_lecturers = (int) db_value('SELECT COUNT(*) FROM lecturers');
$total_finance = (int) db_value('SELECT COUNT(*) FROM finance_officers');
$total_classes = (int) db_value('SELECT COUNT(*) FROM classes');
$total_modules = (int) db_value('SELECT COUNT(*) FROM modules');
$total_semesters = (int) db_value('SELECT COUNT(*) FROM semesters');
$approved_clearances = (int) db_value('SELECT COUNT(*) FROM final_clearance WHERE status = "approved"');
$pending_clearances = (int) db_value('SELECT COUNT(*) FROM students s LEFT JOIN final_clearance f ON f.student_id=s.id WHERE COALESCE(f.status,"pending") <> "approved"');

// Get active users (logged in within 30 days)
$active_users = (int) db_value('
    SELECT COUNT(*) FROM users 
    WHERE last_login_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
');

// Get recent activity
$recent_activities = db_all('
    SELECT id, action, entity, entity_id, created_at 
    FROM activity_logs 
    ORDER BY created_at DESC 
    LIMIT 10
');

?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>Admin Dashboard</h1>
    <p>Welcome back, <?= e(current_user()['name']) ?></p>
</div>

<!-- Statistics Grid -->
<div class="grid grid-col-4">
    <div class="stat-card">
        <div class="stat-card-icon">👥</div>
        <div class="stat-card-value"><?= $total_users ?></div>
        <div class="stat-card-label">Total Users</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">🎓</div>
        <div class="stat-card-value"><?= $total_students ?></div>
        <div class="stat-card-label">Students</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">👨‍🏫</div>
        <div class="stat-card-value"><?= $total_lecturers ?></div>
        <div class="stat-card-label">Lecturers</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">🏫</div>
        <div class="stat-card-value"><?= $total_classes ?></div>
        <div class="stat-card-label">Classes</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">📚</div>
        <div class="stat-card-value"><?= $total_modules ?></div>
        <div class="stat-card-label">Modules</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">📅</div>
        <div class="stat-card-value"><?= $total_semesters ?></div>
        <div class="stat-card-label">Semesters</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">✅</div>
        <div class="stat-card-value"><?= $active_users ?></div>
        <div class="stat-card-label">Active Users (30d)</div>
    </div>
    <div class="stat-card"><div class="stat-card-icon">💼</div><div class="stat-card-value"><?= $total_finance ?></div><div class="stat-card-label">Finance Officers</div></div>
    <div class="stat-card"><div class="stat-card-icon">✓</div><div class="stat-card-value"><?= $approved_clearances ?></div><div class="stat-card-label">Clearances Approved</div></div>
    <div class="stat-card"><div class="stat-card-icon">⌛</div><div class="stat-card-value"><?= $pending_clearances ?></div><div class="stat-card-label">Students Pending Clearance</div></div>
</div>

<!-- Management Sections -->
<div class="grid grid-col-2">
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">Quick Actions</div>
        <div class="card-body">
            <div style="display: grid; gap: 10px; grid-template-columns: 1fr 1fr;">
                <a href="<?= url('admin/users.php') ?>" class="btn btn-primary">Manage Users</a>
                <a href="<?= url('admin/classes.php') ?>" class="btn btn-secondary">Manage Classes</a>
                <a href="<?= url('admin/semesters.php') ?>" class="btn btn-secondary">Manage Semesters</a>
                <a href="<?= url('admin/modules.php') ?>" class="btn btn-secondary">Manage Modules</a>
                <a href="<?= url('admin/clearances.php') ?>" class="btn btn-secondary">Verify Clearances</a>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="card">
        <div class="card-header">System Information</div>
        <div class="card-body">
            <table style="width: 100%; font-size: 13px;">
                <tr>
                    <td style="padding: 5px;"><strong>Application:</strong></td>
                    <td style="padding: 5px;"><?= e(app_config('name')) ?></td>
                </tr>
                <tr>
                    <td style="padding: 5px;"><strong>University:</strong></td>
                    <td style="padding: 5px;"><?= e(app_config('university_name')) ?></td>
                </tr>
                <tr>
                    <td style="padding: 5px;"><strong>PHP Version:</strong></td>
                    <td style="padding: 5px;"><?= phpversion() ?></td>
                </tr>
                <tr>
                    <td style="padding: 5px;"><strong>MySQL Version:</strong></td>
                    <td style="padding: 5px;">
                        <?php
                        try {
                            $version = db_value('SELECT VERSION()');
                            echo e((string) $version);
                        } catch (Exception $e) {
                            echo 'N/A';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px;"><strong>Server Time:</strong></td>
                    <td style="padding: 5px;"><?= date('Y-m-d H:i:s') ?></td>
                </tr>
                <tr>
                    <td style="padding: 5px;"><strong>Production Mode:</strong></td>
                    <td style="padding: 5px;"><?= app_config('production') ? 'Yes' : 'No' ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header">Recent Activity</div>
    <div class="card-body">
        <?php if (!empty($recent_activities)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Entity ID</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activities as $activity): ?>
                        <tr>
                            <td><?= e($activity['action']) ?></td>
                            <td><?= e($activity['entity'] ?? 'N/A') ?></td>
                            <td><?= e($activity['entity_id'] ?? 'N/A') ?></td>
                            <td><?= formatDate($activity['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No recent activity</p>
        <?php endif; ?>
    </div>
    <div class="card-footer">
        <a href="<?= url('admin/logs.php') ?>" class="btn btn-sm btn-secondary">View All Logs</a>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    margin-bottom: 5px;
    color: #333;
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
