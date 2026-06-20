<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

require_role('student');
$page_title = 'My Attendance - SMIS';

// Get student info
$student = db_one('SELECT * FROM students WHERE user_id = :id', ['id' => current_user()['id']]);

if (!$student) {
    redirect('login.php');
}

// Get total attendance records
$total = (int) db_value(
    'SELECT COUNT(*) FROM attendance WHERE student_id = :id',
    ['id' => $student['id']]
);

// Get attendance statistics
$present_count = (int) db_value(
    'SELECT COUNT(*) FROM attendance WHERE student_id = :id AND status = "present"',
    ['id' => $student['id']]
);

$absent_count = (int) db_value(
    'SELECT COUNT(*) FROM attendance WHERE student_id = :id AND status = "absent"',
    ['id' => $student['id']]
);

$late_count = (int) db_value(
    'SELECT COUNT(*) FROM attendance WHERE student_id = :id AND status = "late"',
    ['id' => $student['id']]
);

$attendance_percentage = $total > 0 ? round(($present_count / $total) * 100, 1) : 0;

// Get pagination
$page = max(1, (int) query('page', 1));
$per_page = 10;
$meta = pagination_meta($total, $page, $per_page);

// Get attendance records
$records = db_all('
    SELECT 
        a.id,
        m.code,
        m.title,
        c.name as class_name,
        a.attendance_date,
        a.status,
        a.remarks,
        u.name as lecturer_name
    FROM attendance a
    JOIN modules m ON a.module_id = m.id
    JOIN classes c ON a.class_id = c.id
    JOIN users u ON a.lecturer_id = u.id
    WHERE a.student_id = :id
    ORDER BY a.attendance_date DESC
    LIMIT :limit OFFSET :offset
', [
    'id' => $student['id'],
    'limit' => $per_page,
    'offset' => $meta['offset']
]);

?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>My Attendance</h1>
    <p>Track your attendance across all modules</p>
</div>

<!-- Attendance Summary -->
<div class="grid grid-col-4">
    <div class="stat-card">
        <div class="stat-card-icon">📍</div>
        <div class="stat-card-value"><?= $attendance_percentage ?>%</div>
        <div class="stat-card-label">Attendance Rate</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">✅</div>
        <div class="stat-card-value"><?= $present_count ?></div>
        <div class="stat-card-label">Present</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">❌</div>
        <div class="stat-card-value"><?= $absent_count ?></div>
        <div class="stat-card-label">Absent</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">⏰</div>
        <div class="stat-card-value"><?= $late_count ?></div>
        <div class="stat-card-label">Late</div>
    </div>
</div>

<?php if (!empty($records)): ?>
    <!-- Attendance Records Table -->
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Code</th>
                        <th>Class</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Lecturer</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?= e($record['title']) ?></td>
                            <td><?= e($record['code']) ?></td>
                            <td><?= e($record['class_name']) ?></td>
                            <td><?= formatDate($record['attendance_date']) ?></td>
                            <td>
                                <span style="
                                    display: inline-block;
                                    background-color: <?= match($record['status']) {
                                        'present' => '#28a745',
                                        'absent' => '#dc3545',
                                        'late' => '#ffc107',
                                        default => '#6c757d'
                                    } ?>;
                                    color: <?= $record['status'] === 'late' ? '#333' : 'white' ?>;
                                    padding: 4px 8px;
                                    border-radius: 3px;
                                    font-weight: bold;
                                    font-size: 12px;
                                ">
                                    <?= ucfirst($record['status']) ?>
                                </span>
                            </td>
                            <td><?= e($record['lecturer_name']) ?></td>
                            <td><?= e($record['remarks'] ?? '-') ?></td>
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
                <h1>No Attendance Records</h1>
                <p>No attendance has been recorded yet.</p>
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
