<?php

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;
    if ($user === null) {
        $user = db_one('SELECT id, name, email, role, status, last_login_at FROM users WHERE id = :id', [
            'id' => $_SESSION['user_id'],
        ]);
    }

    return $user;
}

function attempt_login(string $email, string $password): bool
{
    $user = db_one('SELECT * FROM users WHERE email = :email LIMIT 1', ['email' => trim($email)]);
    if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    db_execute('UPDATE users SET last_login_at = NOW() WHERE id = :id', ['id' => $user['id']]);
    audit_log('login', 'users', (string) $user['id']);
    return true;
}

function logout_user(): void
{
    if (current_user()) {
        audit_log('logout', 'users', (string) current_user()['id']);
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function role_home(string $role): string
{
    return match ($role) {
        'admin' => 'admin/dashboard.php',
        'lecturer' => 'lecturer/dashboard.php',
        'finance' => 'finance/dashboard.php',
        'student' => 'student/dashboard.php',
        default => 'login.php',
    };
}

function audit_log(string $action, ?string $entity = null, ?string $entityId = null): void
{
    try {
        db_execute(
            'INSERT INTO activity_logs (user_id, action, entity, entity_id, ip_address, user_agent)
             VALUES (:user_id, :action, :entity, :entity_id, :ip, :agent)',
            [
                'user_id' => $_SESSION['user_id'] ?? null,
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI', 0, 255),
            ]
        );
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
    }
}
