<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

require_role('admin');
$page_title = 'Activity Logs - SMIS';

// Get pagination info
$total = (int) db_value('SELECT COUNT(*) FROM activity_logs');
$page = max(1, (int) query('page', 1));
$per_page = app_config('items_per_page', 10);
$meta = pagination_meta($total, $page, $per_page);

// Get activity logs
$logs = db_all('
    SELECT l.id, l.action, l.entity, l.entity_id, l.ip_address, l.created_at, u.name, u.email
    FROM activity_logs l
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
    LIMIT :limit OFFSET :offset
', [
    'limit' => $per_page,
    'offset' => $meta['offset']
]);

$flashes = flashes();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>Activity Logs</h1>
    <p>Total activities: <?= $total ?></p>
</div>

<!-- Search and Filter -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
            <div>
                <label>Search by User Email</label>
                <input type="text" name="user" value="<?= e(query('user')) ?>" class="form-control" placeholder="Search...">
            </div>
            <div>
                <label>Filter by Action</label>
                <select name="action" class="form-control">
                    <option value="">All Actions</option>
                    <option value="login" <?= query('action') === 'login' ? 'selected' : '' ?>>Login</option>
                    <option value="logout" <?= query('action') === 'logout' ? 'selected' : '' ?>>Logout</option>
                    <option value="create" <?= query('action') === 'create' ? 'selected' : '' ?>>Create</option>
                    <option value="update" <?= query('action') === 'update' ? 'selected' : '' ?>>Update</option>
                    <option value="delete" <?= query('action') === 'delete' ? 'selected' : '' ?>>Delete</option>
                </select>
            </div>
            <div>
                <label>Filter by Entity</label>
                <select name="entity" class="form-control">
                    <option value="">All Entities</option>
                    <option value="users" <?= query('entity') === 'users' ? 'selected' : '' ?>>Users</option>
                    <option value="students" <?= query('entity') === 'students' ? 'selected' : '' ?>>Students</option>
                    <option value="grades" <?= query('entity') === 'grades' ? 'selected' : '' ?>>Grades</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">🔍 Search</button>
        </form>
    </div>
</div>

<?php if (!empty($logs)): ?>
    <!-- Activity Logs Table -->
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Entity ID</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= e($log['name'] ?? 'System') ?></td>
                            <td>
                                <span style="display: inline-block; background-color: #17a2b8; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">
                                    <?= e($log['action']) ?>
                                </span>
                            </td>
                            <td><?= e($log['entity'] ?? 'N/A') ?></td>
                            <td><?= e($log['entity_id'] ?? 'N/A') ?></td>
                            <td><?= e($log['ip_address'] ?? 'N/A') ?></td>
                            <td><?= formatDate($log['created_at']) ?></td>
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
                <h1>No Activity Logs</h1>
                <p>Activity logs will appear here as users interact with the system.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
