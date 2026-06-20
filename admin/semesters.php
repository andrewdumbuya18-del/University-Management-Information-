<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require_role('admin');
$page_title = 'Semesters Management - SMIS';

if (is_post()) {
    enforce_csrf();
    $id = (int) post('id');
    $name = trim((string) post('name'));
    $academic_year = trim((string) post('academic_year'));
    $starts_on = (string) post('starts_on');
    $ends_on = (string) post('ends_on');
    $status = (string) post('status');
    $errors = validate_required(
        compact('name', 'academic_year', 'starts_on', 'ends_on', 'status'),
        ['name' => 'Name', 'academic_year' => 'Academic year', 'starts_on' => 'Start date', 'ends_on' => 'End date', 'status' => 'Status']
    );
    $errors += validate_in(['status' => $status], 'status', ['open', 'closed'], 'Status');
    if ($starts_on !== '' && $ends_on !== '' && $ends_on < $starts_on) {
        $errors['ends_on'] = 'End date must be on or after the start date.';
    }
    if (!$errors) {
        $duplicate = (int) db_value(
            'SELECT COUNT(*) FROM semesters WHERE name = :name AND academic_year = :year AND id <> :id',
            ['name' => $name, 'year' => $academic_year, 'id' => $id]
        );
        if ($duplicate > 0) {
            $errors['name'] = 'That semester already exists for this academic year.';
        }
    }
    if (!$errors) {
        if ($id > 0) {
            db_execute(
                'UPDATE semesters SET name = :name, academic_year = :year, starts_on = :starts, ends_on = :ends, status = :status WHERE id = :id',
                ['name' => $name, 'year' => $academic_year, 'starts' => $starts_on, 'ends' => $ends_on, 'status' => $status, 'id' => $id]
            );
            audit_log('update', 'semesters', (string) $id);
        } else {
            db_execute(
                'INSERT INTO semesters (name, academic_year, starts_on, ends_on, status) VALUES (:name, :year, :starts, :ends, :status)',
                ['name' => $name, 'year' => $academic_year, 'starts' => $starts_on, 'ends' => $ends_on, 'status' => $status]
            );
            audit_log('create', 'semesters', db_insert_id());
        }
        respond_success('Semester saved successfully.', 'admin/semesters.php');
    }
}

$editing_semester = null;
if (query('action') === 'edit') {
    $editing_semester = db_one('SELECT * FROM semesters WHERE id = :id', ['id' => (int) query('id')]);
}

$total = (int) db_value('SELECT COUNT(*) FROM semesters');
$page = max(1, (int) query('page', 1));
$per_page = 10;
$meta = pagination_meta($total, $page, $per_page);

$semesters = db_all('
    SELECT id, name, academic_year, starts_on, ends_on, status
    FROM semesters
    ORDER BY starts_on DESC, id DESC
    LIMIT :limit OFFSET :offset
', ['limit' => $per_page, 'offset' => $meta['offset']]);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>Semesters Management</h1>
            <p>Total semesters: <?= $total ?></p>
        </div>
        <a href="<?= url('admin/semesters.php?action=create') ?>" class="btn btn-primary">➕ Add Semester</a>
    </div>
</div>

<?php if (in_array(query('action'), ['create', 'edit'], true)): ?>
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header"><?= $editing_semester ? 'Edit Semester' : 'Add Semester' ?></div>
        <div class="card-body">
            <?php if (!empty($errors)): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) ($editing_semester['id'] ?? 0) ?>">
                <div class="grid grid-col-2">
                    <div class="form-group"><label>Name</label><input class="form-control" name="name" required value="<?= e(post('name', $editing_semester['name'] ?? '')) ?>"></div>
                    <div class="form-group"><label>Academic Year</label><input class="form-control" name="academic_year" required placeholder="2026/2027" value="<?= e(post('academic_year', $editing_semester['academic_year'] ?? '')) ?>"></div>
                    <div class="form-group"><label>Start Date</label><input class="form-control" type="date" name="starts_on" required value="<?= e(post('starts_on', $editing_semester['starts_on'] ?? '')) ?>"></div>
                    <div class="form-group"><label>End Date</label><input class="form-control" type="date" name="ends_on" required value="<?= e(post('ends_on', $editing_semester['ends_on'] ?? '')) ?>"></div>
                    <div class="form-group"><label>Status</label><select class="form-control" name="status"><option value="open" <?= post('status', $editing_semester['status'] ?? 'open') === 'open' ? 'selected' : '' ?>>Open</option><option value="closed" <?= post('status', $editing_semester['status'] ?? 'open') === 'closed' ? 'selected' : '' ?>>Closed</option></select></div>
                </div>
                <button class="btn btn-primary" type="submit">Save Semester</button>
                <a class="btn btn-secondary" href="<?= url('admin/semesters.php') ?>">Cancel</a>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($semesters)): ?>
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Academic Year</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($semesters as $sem): ?>
                        <tr>
                            <td><?= e($sem['name']) ?></td>
                            <td><?= e($sem['academic_year']) ?></td>
                            <td><?= $sem['starts_on'] ? formatDate($sem['starts_on']) : '-' ?></td>
                            <td><?= $sem['ends_on'] ? formatDate($sem['ends_on']) : '-' ?></td>
                            <td>
                                <span style="display: inline-block; background-color: <?= $sem['status'] === 'open' ? '#28a745' : '#6c757d' ?>; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">
                                    <?= ucfirst($sem['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= url('admin/semesters.php?action=edit&id=' . $sem['id']) ?>" class="action-btn action-edit">Edit</a>
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
                <h1>No Semesters Found</h1>
                <a href="<?= url('admin/semesters.php?action=create') ?>" class="btn btn-primary">Add First Semester</a>
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
}

.action-edit {
    background-color: #17a2b8;
    color: white;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
