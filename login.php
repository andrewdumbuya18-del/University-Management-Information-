<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

// If already logged in, redirect to dashboard
if (current_user()) {
    redirect(role_home(current_user()['role']));
}

$errors = [];

if (is_post()) {
    enforce_csrf();
    
    $errors = validate_required($_POST, ['email' => 'Email', 'password' => 'Password']);
    if (empty($errors)) {
        $errors += validate_email_field($_POST);
    }
    
    if (empty($errors)) {
        if (attempt_login(post('email'), post('password'))) {
            $user = current_user();
            set_flash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect(role_home($user['role']));
        } else {
            $errors['login'] = 'Invalid email or password.';
        }
    }
    
    if (!empty($errors)) {
        set_flash('error', 'Please correct the errors below.');
    }
}

$flashes = flashes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMIS</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <a href="<?= url('/') ?>" class="login-close" aria-label="Return to landing page">×</a>
            <div class="login-header">
                <div class="login-logo" aria-hidden="true">S</div>
                <h1><?= e(app_config('short_name')) ?></h1>
                <p><?= e(app_config('university_name')) ?></p>
            </div>

            <?php foreach ($flashes as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endforeach; ?>

            <?php if (!empty($errors['login'])): ?>
                <div class="alert alert-error">
                    <?= e($errors['login']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= e(post('email')) ?>"
                        class="form-control <?= !empty($errors['email']) ? 'error' : '' ?>"
                        placeholder="Enter your email"
                        required
                    >
                    <?php if (!empty($errors['email'])): ?>
                        <small class="error-text"><?= e($errors['email']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control <?= !empty($errors['password']) ? 'error' : '' ?>"
                        placeholder="Enter your password"
                        required
                    >
                    <?php if (!empty($errors['password'])): ?>
                        <small class="error-text"><?= e($errors['password']) ?></small>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Sign In
                </button>
            </form>

            <div class="login-footer">
                <p class="text-muted text-center">
                    For demo credentials, use:<br>
                    Email: <strong>admin@smis.test</strong><br>
                    Password: <strong>password</strong>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
