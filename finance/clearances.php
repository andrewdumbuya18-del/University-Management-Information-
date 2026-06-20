<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_role('finance');
$page_title = 'Finance Clearances - SMIS';

$action = query('action');
$student_id = (int) query('id');

if (is_post()) {
    enforce_csrf();
    
    $post_action = post('action');
    $post_student_id = (int) post('student_id');
    $status = post('status');
    $remarks = post('remarks', '');
    
    if ($post_action === 'update_clearance') {
        if (!in_array($status, ['pending', 'cleared', 'not_cleared'], true)
            || !db_value('SELECT COUNT(*) FROM students WHERE id = :id', ['id' => $post_student_id])) {
            respond_error('Invalid clearance update.');
        }
        $existing = db_one('SELECT * FROM finance_clearance WHERE student_id = :id', ['id' => $post_student_id]);
        
        if ($existing) {
            db_execute(
                'UPDATE finance_clearance SET status = :status, remarks = :remarks, cleared_by = :by, cleared_at = NOW() WHERE student_id = :sid',
                ['status' => $status, 'remarks' => $remarks, 'by' => current_user()['id'], 'sid' => $post_student_id]
            );
        } else {
            db_execute(
                'INSERT INTO finance_clearance (student_id, status, remarks, cleared_by, cleared_at) VALUES (:sid, :status, :remarks, :by, NOW())',
                ['sid' => $post_student_id, 'status' => $status, 'remarks' => $remarks, 'by' => current_user()['id']]
            );
        }
        
        audit_log('update_clearance', 'students', (string) $post_student_id);
        set_flash('success', 'Clearance status updated.');
        redirect('finance/clearances.php');
    }
}

$editing_student = null;
if ($action === 'edit' && $student_id > 0) {
    $editing_student = db_one(
        'SELECT s.id, s.student_number, u.name, fc.status, fc.remarks
         FROM students s
         JOIN users u ON u.id = s.user_id
         LEFT JOIN finance_clearance fc ON fc.student_id = s.id
         WHERE s.id = :id',
        ['id' => $student_id]
    );
}

// Get pagination
$total = (int) db_value('SELECT COUNT(*) FROM students');
$page = max(1, (int) query('page', 1));
$per_page = 10;
$meta = pagination_meta($total, $page, $per_page);

// Get students with clearance status
$students = db_all('
    SELECT 
        s.id,
        u.name,
        s.student_number,
        fc.status as clearance_status,
        fc.remarks,
        fc.cleared_at
    FROM students s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN finance_clearance fc ON s.id = fc.student_id
    ORDER BY u.name ASC
    LIMIT :limit OFFSET :offset
', ['limit' => $per_page, 'offset' => $meta['offset']]);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>Finance Clearances</h1>
    <p>Total students: <?= $total ?></p>
</div>

<?php if ($editing_student): ?>
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">Update Clearance — <?= e($editing_student['name']) ?></div>
        <div class="card-body">
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_clearance">
                <input type="hidden" name="student_id" value="<?= (int) $editing_student['id'] ?>">
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status" required>
                        <?php foreach (['pending' => 'Pending', 'cleared' => 'Cleared', 'not_cleared' => 'Not Cleared'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= ($editing_student['status'] ?? 'pending') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Remarks</label>
                    <textarea class="form-control" name="remarks"><?= e($editing_student['remarks'] ?? '') ?></textarea>
                </div>
                <button class="btn btn-primary" type="submit">Save Clearance</button>
                <a class="btn btn-secondary" href="<?= url('finance/clearances.php') ?>">Cancel</a>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($students)): ?>
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student Number</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= e($student['student_number']) ?></td>
                            <td><?= e($student['name']) ?></td>
                            <td>
                                <?php if ($student['clearance_status']): ?>
                                    <span style="display: inline-block; background-color: <?= $student['clearance_status'] === 'cleared' ? '#28a745' : '#dc3545' ?>; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">
                                        <?= ucfirst($student['clearance_status']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">Not set</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($student['remarks'] ?? '-') ?></td>
                            <td><?= $student['cleared_at'] ? formatDate($student['cleared_at']) : '-' ?></td>
                            <td>
                                <a href="<?= url('finance/clearances.php?action=edit&id=' . $student['id']) ?>" class="action-btn action-edit">Update</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php render_pagination($meta); ?>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <h1>No Students Found</h1>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
.page-header {
    margin-bottom: 20px;
}

.action-btn {
    padding: 4px 8px;
    border-radius: 3px;
    text-decoration: none;
    background-color: #17a2b8;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 40px;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
