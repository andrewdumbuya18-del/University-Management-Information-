<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_role('lecturer');
$page_title = 'Lecturer Dashboard - SMIS';

// Get lecturer info
$lecturer = db_one('SELECT * FROM lecturers WHERE user_id = :id', ['id' => current_user()['id']]);

if (!$lecturer) {
    set_flash('error', 'Lecturer profile not found.');
    redirect('login.php');
}

// Get statistics
$total_modules = (int) db_value(
    'SELECT COUNT(*) FROM lecturer_modules WHERE lecturer_id = :id',
    ['id' => $lecturer['id']]
);

$total_students = (int) db_value(
    'SELECT COUNT(DISTINCT sm.student_id)
     FROM lecturer_modules lm
     JOIN student_modules sm
       ON sm.module_id = lm.module_id
      AND sm.semester_id = lm.semester_id
     JOIN students s
       ON s.id = sm.student_id
      AND s.class_id = lm.class_id
     WHERE lm.lecturer_id = :id',
    ['id' => $lecturer['id']]
);

$grades_recorded = (int) db_value(
    'SELECT COUNT(*) FROM grades WHERE lecturer_id = :id',
    ['id' => $lecturer['id']]
);

$attendance_records = (int) db_value(
    'SELECT COUNT(*) FROM attendance WHERE lecturer_id = :id',
    ['id' => $lecturer['id']]
);

// Get upcoming classes
$upcoming_classes = db_all('
    SELECT lm.id, c.name, m.code, m.title
    FROM lecturer_modules lm
    JOIN classes c ON lm.class_id = c.id
    JOIN modules m ON lm.module_id = m.id
    WHERE lm.lecturer_id = :id
    ORDER BY m.code
    LIMIT 5
', ['id' => $lecturer['id']]);

?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>Welcome, <?= e(current_user()['name']) ?></h1>
    <p>Staff Number: <?= e($lecturer['staff_number']) ?> | Department: <?= e($lecturer['department'] ?? 'N/A') ?></p>
</div>

<!-- Statistics -->
<div class="grid grid-col-4">
    <div class="stat-card">
        <div class="stat-card-icon">📚</div>
        <div class="stat-card-value"><?= $total_modules ?></div>
        <div class="stat-card-label">My Modules</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">👥</div>
        <div class="stat-card-value"><?= $total_students ?></div>
        <div class="stat-card-label">Total Students</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">📊</div>
        <div class="stat-card-value"><?= $grades_recorded ?></div>
        <div class="stat-card-label">Grades Recorded</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">📍</div>
        <div class="stat-card-value"><?= $attendance_records ?></div>
        <div class="stat-card-label">Attendance Records</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-col-2">
    <div class="card">
        <div class="card-header">Quick Actions</div>
        <div class="card-body">
            <div style="display: grid; gap: 10px;">
                <a href="<?= url('profile.php') ?>" class="btn btn-primary">View My Profile</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Assigned Classes</div>
        <div class="card-body">
            <?php if (!empty($upcoming_classes)): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach (array_slice($upcoming_classes, 0, 3) as $class): ?>
                        <li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                            <strong><?= e($class['code']) ?></strong><br>
                            <small><?= e($class['title']) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">No classes assigned yet</p>
            <?php endif; ?>
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
    font-size: 13px;
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

.text-muted {
    color: #999;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
