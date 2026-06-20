<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

require_login();
$page_title = 'Settings - SMIS';

$errors = [];
$message = null;

if (is_post()) {
    enforce_csrf();
    
    $action = post('action');
    $new_password = post('new_password', '');
    $confirm_password = post('confirm_password', '');
    $current_password = post('current_password', '');
    
    if ($action === 'change_password') {
        // Validate inputs
        if (empty($current_password)) {
            $errors['current_password'] = 'Current password is required.';
        }
        if (strlen($new_password) < 8) {
            $errors['new_password'] = 'Password must be at least 8 characters.';
        }
        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
        
        if (empty($errors)) {
            // Verify current password
            $user = db_one('SELECT * FROM users WHERE id = :id', ['id' => current_user()['id']]);
            if (!password_verify($current_password, $user['password'])) {
                $errors['current_password'] = 'Current password is incorrect.';
            } else {
                // Update password
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                db_execute('UPDATE users SET password = :password WHERE id = :id', [
                    'password' => $hashed,
                    'id' => current_user()['id']
                ]);
                set_flash('success', 'Password changed successfully.');
                audit_log('change_password', 'users', (string) current_user()['id']);
                redirect('settings.php');
            }
        }
    }
}

$flashes = flashes();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="page-header">
    <h1>Settings</h1>
</div>

<div class="card" style="max-width: 500px;">
    <div class="card-header">Change Password</div>
    <div class="card-body">
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="change_password">

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input 
                    type="password" 
                    id="current_password"
                    name="current_password" 
                    class="form-control <?= !empty($errors['current_password']) ? 'error' : '' ?>"
                    required
                >
                <?php if (!empty($errors['current_password'])): ?>
                    <small class="error-text"><?= e($errors['current_password']) ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input 
                    type="password" 
                    id="new_password"
                    name="new_password" 
                    class="form-control <?= !empty($errors['new_password']) ? 'error' : '' ?>"
                    required
                >
                <?php if (!empty($errors['new_password'])): ?>
                    <small class="error-text"><?= e($errors['new_password']) ?></small>
                <?php endif; ?>
                <small class="text-muted">Minimum 8 characters</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password"
                    name="confirm_password" 
                    class="form-control <?= !empty($errors['confirm_password']) ? 'error' : '' ?>"
                    required
                >
                <?php if (!empty($errors['confirm_password'])): ?>
                    <small class="error-text"><?= e($errors['confirm_password']) ?></small>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Update Password</button>
                <a href="<?= url('profile.php') ?>" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 20px;
}

.page-header h1 {
    margin-bottom: 5px;
}

.text-muted {
    color: #999;
    font-size: 12px;
}

.error-text {
    display: block;
    color: #dc3545;
    font-size: 12px;
    margin-top: 3px;
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
