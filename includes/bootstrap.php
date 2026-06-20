<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once __DIR__ . '/functions.php';

date_default_timezone_set(app_config('timezone', 'Africa/Freetown'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (app_config('production', false)) {
    ini_set('display_errors', '0');
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

set_exception_handler(function (Throwable $exception): void {
    error_log((string) $exception);
    http_response_code(500);
    if (wants_json()) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'message' => 'A system error occurred.']);
        exit;
    }

    include __DIR__ . '/header.php';
    echo '<main class="content"><section class="empty-state"><h1>System error</h1><p>Please contact the administrator if this continues.</p></section></main>';
    include __DIR__ . '/footer.php';
    exit;
});

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/pagination.php';
