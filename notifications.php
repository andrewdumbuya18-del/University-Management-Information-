<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

require_login();
$page_title = 'Notifications - SMIS';

// Handle marking as read
if (is_post()) {
    enforce_csrf();
    
    $action = post('action');
    $notification_id = (int) post('id');
    
    if ($action === 'mark_read') {
        db_execute(
            'UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id',
            ['id' => $notification_id, 'user_id' => current_user()['id']]
        );
    } elseif ($action === 'mark_all_read') {
        db_execute(
            'UPDATE notifications SET is_read = 1 WHERE user_id = :id',
            ['id' => current_user()['id']]
        );
    } elseif ($action === 'delete') {
        db_execute(
            'DELETE FROM notifications WHERE id = :id AND user_id = :user_id',
            ['id' => $notification_id, 'user_id' => current_user()['id']]
        );
    }
    
    redirect('notifications.php');
}

// Get pagination info
$total = (int) db_value('SELECT COUNT(*) FROM notifications WHERE user_id = :id', ['id' => current_user()['id']]);
$page = max(1, (int) query('page', 1));
$per_page = 10;
$meta = pagination_meta($total, $page, $per_page);

// Get notifications
$notifications = db_all('
    SELECT id, title, message, is_read, created_at
    FROM notifications
    WHERE user_id = :id
    ORDER BY created_at DESC
    LIMIT :limit OFFSET :offset
', [
    'id' => current_user()['id'],
    'limit' => $per_page,
    'offset' => $meta['offset']
]);

$unread_count = (int) db_value('SELECT COUNT(*) FROM notifications WHERE user_id = :id AND is_read = 0', ['id' => current_user()['id']]);

$flashes = flashes();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1>Notifications</h1>
        <p>Unread: <?= $unread_count ?> | Total: <?= $total ?></p>
    </div>
    <?php if ($unread_count > 0): ?>
        <form method="POST" style="display: inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="btn btn-sm btn-secondary">Mark All as Read</button>
        </form>
    <?php endif; ?>
</div>

<?php if (!empty($notifications)): ?>
    <!-- Notifications List -->
    <div style="display: grid; gap: 10px;">
        <?php foreach ($notifications as $notification): ?>
            <div class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>" style="
                background: white;
                padding: 15px;
                border-radius: 6px;
                border-left: 4px solid <?= $notification['is_read'] ? '#dee2e6' : '#007bff' ?>;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            ">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 5px 0; color: #333;">
                            <?= e($notification['title']) ?>
                            <?php if (!$notification['is_read']): ?>
                                <span style="display: inline-block; background: #007bff; color: white; font-size: 10px; padding: 2px 6px; border-radius: 3px; margin-left: 5px;">NEW</span>
                            <?php endif; ?>
                        </h4>
                        <p style="margin: 5px 0; color: #666; line-height: 1.5;">
                            <?= e($notification['message']) ?>
                        </p>
                        <small style="color: #999;">
                            <?= formatDateTime($notification['created_at']) ?>
                        </small>
                    </div>
                    <div style="display: flex; gap: 5px; margin-left: 10px;">
                        <?php if (!$notification['is_read']): ?>
                            <form method="POST" style="display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="id" value="<?= $notification['id'] ?>">
                                <button type="submit" class="btn btn-sm" style="background: #28a745; color: white; border: none; cursor: pointer;">Mark Read</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display: inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $notification['id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background: #dc3545; color: white; border: none; cursor: pointer;">Delete</button>
                        </form>
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
                <h1>No Notifications</h1>
                <p>You're all caught up!</p>
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
    font-size: 12px;
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

<?php include __DIR__ . '/includes/footer.php'; ?>
