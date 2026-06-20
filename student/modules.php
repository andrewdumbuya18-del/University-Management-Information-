<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

require_role('student');
$page_title = 'My Modules - SMIS';

// Get student info
$student = db_one('SELECT * FROM students WHERE user_id = :id', ['id' => current_user()['id']]);

if (!$student) {
    set_flash('error', 'Student profile not found.');
    redirect('login.php');
}

// Get pagination info
$total = (int) db_value(
    'SELECT COUNT(*) FROM student_modules WHERE student_id = :id',
    ['id' => $student['id']]
);

$page = max(1, (int) query('page', 1));
$per_page = 10;
$meta = pagination_meta($total, $page, $per_page);

// Get student modules
$modules = db_all('
    SELECT 
        sm.id,
        m.id as module_id,
        m.code,
        m.title,
        m.credits,
        sm.semester_id,
        s.name as semester_name,
        COUNT(DISTINCT a.id) as attendance_count,
        COUNT(DISTINCT CASE WHEN a.status = "present" THEN a.id END) as present_count,
        AVG(g.final_grade) as avg_grade
    FROM student_modules sm
    JOIN modules m ON sm.module_id = m.id
    JOIN semesters s ON sm.semester_id = s.id
    LEFT JOIN attendance a ON a.student_id = sm.student_id AND a.module_id = sm.module_id
    LEFT JOIN grades g ON g.student_id = sm.student_id AND g.module_id = sm.module_id
    WHERE sm.student_id = :id
    GROUP BY sm.id, m.id, m.code, m.title, m.credits, sm.semester_id, s.name, s.starts_on
    ORDER BY s.starts_on DESC, m.code ASC
    LIMIT :limit OFFSET :offset
', [
    'id' => $student['id'],
    'limit' => $per_page,
    'offset' => $meta['offset']
]);

?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>My Modules</h1>
    <p>Total modules enrolled: <?= $total ?></p>
</div>

<?php if (!empty($modules)): ?>
    <div style="display: grid; gap: 15px;">
        <?php foreach ($modules as $module): ?>
            <div class="card">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <h3 style="margin: 0 0 5px 0;">
                                <?= e($module['code']) ?> - <?= e($module['title']) ?>
                            </h3>
                            <p style="margin: 5px 0; color: #666; font-size: 13px;">
                                <strong>Semester:</strong> <?= e($module['semester_name']) ?> | 
                                <strong>Credits:</strong> <?= $module['credits'] ?> | 
                                <strong>Attendance:</strong> <?= $module['present_count'] ?? 0 ?> / <?= $module['attendance_count'] ?? 0 ?>
                            </p>
                            <?php if ($module['avg_grade']): ?>
                                <p style="margin: 5px 0; color: #28a745; font-weight: 600;">
                                    <strong>Average Grade:</strong> <?= round($module['avg_grade'], 2) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <a href="<?= url('student/modules.php?action=view&id=' . $module['module_id']) ?>" class="btn btn-sm btn-primary">
                            View Details →
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php render_pagination($meta); ?>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <h1>No Modules Enrolled</h1>
                <p>You are not currently enrolled in any modules.</p>
                <a href="<?= url('student/dashboard.php') ?>" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
.page-header {
    margin-bottom: 20px;
}

.page-header h1 {
    margin-bottom: 5px;
}

.page-header p {
    color: #666;
    font-size: 13px;
}

h3 {
    font-size: 16px;
    color: #333;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state h1 {
    font-size: 24px;
    color: #666;
    margin-bottom: 10px;
}

.empty-state p {
    color: #999;
    margin-bottom: 20px;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
