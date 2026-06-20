<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

require_role('student');
$page_title = 'My Grades - SMIS';

// Get student info
$student = db_one('SELECT * FROM students WHERE user_id = :id', ['id' => current_user()['id']]);

if (!$student) {
    redirect('login.php');
}

// Get pagination info
$total = (int) db_value(
    'SELECT COUNT(*) FROM grades WHERE student_id = :id',
    ['id' => $student['id']]
);

$page = max(1, (int) query('page', 1));
$per_page = 10;
$meta = pagination_meta($total, $page, $per_page);

// Get grades
$grades = db_all('
    SELECT 
        g.id,
        m.code,
        m.title,
        u.name as lecturer_name,
        g.coursework,
        g.examination,
        g.final_grade,
        g.letter_grade,
        g.remarks,
        g.created_at
    FROM grades g
    JOIN modules m ON g.module_id = m.id
    JOIN users u ON g.lecturer_id = u.id
    WHERE g.student_id = :id
    ORDER BY g.created_at DESC
    LIMIT :limit OFFSET :offset
', [
    'id' => $student['id'],
    'limit' => $per_page,
    'offset' => $meta['offset']
]);

// Get average grade
$average = db_value('SELECT AVG(final_grade) FROM grades WHERE student_id = :id', ['id' => $student['id']]) ?? 0;

?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>My Grades</h1>
    <p>Total grades recorded: <?= $total ?> | Average: <strong><?= round($average, 2) ?></strong></p>
</div>

<?php if (!empty($grades)): ?>
    <!-- Grades Table -->
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Module Code</th>
                        <th>Module Title</th>
                        <th>Coursework</th>
                        <th>Examination</th>
                        <th>Final Grade</th>
                        <th>Letter</th>
                        <th>Lecturer</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grades as $grade): ?>
                        <tr>
                            <td><?= e($grade['code']) ?></td>
                            <td><?= e($grade['title']) ?></td>
                            <td><?= round($grade['coursework'], 1) ?></td>
                            <td><?= round($grade['examination'], 1) ?></td>
                            <td><strong><?= round($grade['final_grade'], 1) ?></strong></td>
                            <td>
                                <span style="
                                    display: inline-block;
                                    background-color: <?= match($grade['letter_grade']) {
                                        'A' => '#28a745',
                                        'B' => '#17a2b8',
                                        'C' => '#ffc107',
                                        'D' => '#fd7e14',
                                        'F' => '#dc3545',
                                        default => '#6c757d'
                                    } ?>;
                                    color: white;
                                    padding: 4px 8px;
                                    border-radius: 3px;
                                    font-weight: bold;
                                ">
                                    <?= e($grade['letter_grade'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td><?= e($grade['lecturer_name']) ?></td>
                            <td><?= formatDate($grade['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php render_pagination($meta); ?>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <h1>No Grades Yet</h1>
                <p>Your grades will appear here once lecturers have entered them.</p>
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
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
