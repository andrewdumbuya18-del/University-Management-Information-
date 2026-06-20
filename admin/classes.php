<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

require_role('admin');
$page_title = 'Classes Management - SMIS';

$errors = [];
$success = false;

// Handle form submission
if (is_post()) {
    enforce_csrf();
    
    $action = post('action');
    $class_id = (int) post('id');
    
    if ($action === 'delete' && $class_id > 0) {
        try {
            db_execute('DELETE FROM classes WHERE id = :id', ['id' => $class_id]);
            set_flash('success', 'Class deleted successfully.');
            audit_log('delete', 'classes', (string) $class_id);
            redirect('admin/classes.php');
        } catch (Exception $e) {
            set_flash('error', 'Cannot delete class with existing records.');
        }
    } elseif ($action === 'save') {
        $name = trim((string) post('name'));
        $description = trim((string) post('description', ''));
        
        $errors = validate_required(['name' => $name], ['name' => 'Class Name']);
        
        if (empty($errors)) {
            // Check if name already exists
            $exists = db_value(
                'SELECT COUNT(*) FROM classes WHERE name = :name AND id <> :id',
                ['name' => $name, 'id' => $class_id]
            );
            if ($exists > 0) {
                $errors['name'] = 'A class with this name already exists.';
            }
        }
        
        if (empty($errors)) {
            if ($class_id > 0) {
                // Update
                db_execute(
                    'UPDATE classes SET name = :name, description = :desc WHERE id = :id',
                    ['name' => $name, 'desc' => $description, 'id' => $class_id]
                );
                set_flash('success', 'Class updated successfully.');
                audit_log('update', 'classes', (string) $class_id);
            } else {
                // Create
                db_execute(
                    'INSERT INTO classes (name, description) VALUES (:name, :desc)',
                    ['name' => $name, 'desc' => $description]
                );
                set_flash('success', 'Class created successfully.');
                audit_log('create', 'classes', db_insert_id());
            }
            redirect('admin/classes.php');
        }
    }
}

$editing_class = null;
if (query('action') === 'edit') {
    $editing_class = db_one('SELECT id, name, description FROM classes WHERE id = :id', [
        'id' => (int) query('id'),
    ]);
}

// Get pagination info
$total = (int) db_value('SELECT COUNT(*) FROM classes');
$page = max(1, (int) query('page', 1));
$per_page = 10;
$meta = pagination_meta($total, $page, $per_page);

// Get classes list
$classes = db_all('
    SELECT id, name, description, created_at 
    FROM classes 
    ORDER BY created_at DESC 
    LIMIT :limit OFFSET :offset
', [
    'limit' => $per_page,
    'offset' => $meta['offset']
]);

$flashes = flashes();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>Classes Management</h1>
            <p>Total classes: <?= $total ?></p>
        </div>
        <a href="<?= url('admin/classes.php?action=create') ?>" class="btn btn-primary">➕ Add New Class</a>
    </div>
</div>

<?php if (in_array(query('action'), ['create', 'edit'], true)): ?>
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header"><?= $editing_class ? 'Edit Class' : 'Add Class' ?></div>
        <div class="card-body">
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= (int) ($editing_class['id'] ?? 0) ?>">
                <div class="form-group">
                    <label for="name">Class Name</label>
                    <input id="name" name="name" class="form-control" required value="<?= e(post('name', $editing_class['name'] ?? '')) ?>">
                    <?php if (!empty($errors['name'])): ?><small class="error-text"><?= e($errors['name']) ?></small><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control"><?= e(post('description', $editing_class['description'] ?? '')) ?></textarea>
                </div>
                <button class="btn btn-primary" type="submit">Save Class</button>
                <a class="btn btn-secondary" href="<?= url('admin/classes.php') ?>">Cancel</a>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($classes)): ?>
    <!-- Classes Table -->
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?= e($class['name']) ?></td>
                            <td><?= e(substr($class['description'] ?? '', 0, 50)) ?></td>
                            <td><?= formatDate($class['created_at']) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url('admin/classes.php?action=edit&id=' . $class['id']) ?>" class="action-btn action-edit">Edit</a>
                                    <form method="POST" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $class['id'] ?>">
                                        <button type="submit" class="action-btn action-delete" onclick="return confirm('Delete this class?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
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
                <h1>No Classes Found</h1>
                <a href="<?= url('admin/classes.php?action=create') ?>" class="btn btn-primary">Add First Class</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
