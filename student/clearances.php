<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

require_role('student');
$page_title = 'My Clearances - SMIS';

// Get student info
$student = db_one('SELECT * FROM students WHERE user_id = :id', ['id' => current_user()['id']]);

if (!$student) {
    redirect('login.php');
}

// Get clearance statuses
$finance_clearance = db_one('SELECT * FROM finance_clearance WHERE student_id = :id', ['id' => $student['id']]);
$registration_clearance = db_one('SELECT * FROM registration_clearance WHERE student_id = :id', ['id' => $student['id']]);
$exam_clearance = db_one('SELECT * FROM exam_clearance WHERE student_id = :id', ['id' => $student['id']]);
$final_clearance = db_one('SELECT * FROM final_clearance WHERE student_id = :id', ['id' => $student['id']]);

$clearances = [
    ['name' => 'Finance Clearance', 'data' => $finance_clearance, 'icon' => '💰'],
    ['name' => 'Registration Clearance', 'data' => $registration_clearance, 'icon' => '📝'],
    ['name' => 'Exam Clearance', 'data' => $exam_clearance, 'icon' => '✏️'],
    ['name' => 'Final Clearance', 'data' => $final_clearance, 'icon' => '✅'],
];

?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>My Clearances</h1>
    <p>Track your clearance status across different departments</p>
</div>

<div style="display: grid; gap: 15px;">
    <?php foreach ($clearances as $clearance): ?>
        <?php $data = $clearance['data']; ?>
        <div class="card">
            <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0 0 10px 0; font-size: 18px;">
                        <?= $clearance['icon'] ?> <?= $clearance['name'] ?>
                    </h3>
                    <?php if ($data): ?>
                        <div style="margin: 10px 0;">
                            <p style="margin: 5px 0;">
                                <strong>Status:</strong>
                                <span style="
                                    display: inline-block;
                                    background-color: <?= match($data['status']) {
                                        'approved', 'cleared' => '#28a745',
                                        'pending' => '#ffc107',
                                        'rejected', 'not_cleared' => '#dc3545',
                                        default => '#6c757d'
                                    } ?>;
                                    color: <?= in_array($data['status'], ['approved', 'cleared']) || $data['status'] === 'rejected' || $data['status'] === 'not_cleared' ? 'white' : '#333' ?>;
                                    padding: 4px 8px;
                                    border-radius: 3px;
                                    font-weight: bold;
                                    font-size: 12px;
                                ">
                                    <?= ucfirst($data['status']) ?>
                                </span>
                            </p>
                            <?php if ($data['remarks']): ?>
                                <p style="margin: 5px 0; color: #666;">
                                    <strong>Remarks:</strong> <?= e($data['remarks']) ?>
                                </p>
                            <?php endif; ?>
                            <?php $processed_at = $data['approved_at'] ?? $data['cleared_at'] ?? null; ?>
                            <?php if ($processed_at): ?>
                                <p style="margin: 5px 0; color: #999; font-size: 12px;">
                                    Processed on: <?= formatDateTime($processed_at) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #999; margin: 0;">
                            No clearance record found
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

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
    color: #333;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
