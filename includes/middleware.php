<?php

function require_login(): void
{
    if (!current_user()) {
        set_flash('error', 'Please sign in to continue.');
        redirect('login.php');
    }

    if (current_user()['status'] !== 'active') {
        logout_user();
        set_flash('error', 'Your account is inactive. Contact the administrator.');
        redirect('login.php');
    }
}

function require_role(string|array $roles): void
{
    require_login();
    $roles = (array) $roles;
    if (!in_array(current_user()['role'], $roles, true)) {
        http_response_code(403);
        include __DIR__ . '/header.php';
        echo '<main class="content"><section class="empty-state"><h1>Access denied</h1><p>You do not have permission to view this page.</p></section></main>';
        include __DIR__ . '/footer.php';
        exit;
    }
}
