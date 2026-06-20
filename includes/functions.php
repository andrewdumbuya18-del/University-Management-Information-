<?php

function app_config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/app.php';
    }

    if ($key === null) {
        return $config;
    }

    $value = $config;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }

    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim((string) app_config('base_url', ''), '/');
    $path = ltrim($path, '/');
    return $path === '' ? ($base === '' ? '/' : $base) : $base . '/' . $path;
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function current_request_path(): string
{
    $path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $base = rtrim((string) app_config('base_url', ''), '/');
    if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
        $path = substr($path, strlen($base));
    }

    return ltrim($path, '/');
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function post(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function query(string $key, mixed $default = ''): mixed
{
    return $_GET[$key] ?? $default;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function wants_json(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}

function respond_success(string $message, ?string $redirect = null, array $extra = []): never
{
    if (wants_json()) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['ok' => true, 'message' => $message, 'redirect' => $redirect ? url($redirect) : null], $extra));
        exit;
    }

    set_flash('success', $message);
    if ($redirect) {
        redirect($redirect);
    } else {
        redirect(current_request_path());
    }
    exit;
}

function respond_error(string $message, array $errors = []): never
{
    if (wants_json()) {
        http_response_code(422);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'message' => $message, 'errors' => $errors]);
        exit;
    }

    set_flash('error', $message);
    redirect(current_request_path());
}

function page_number(): int
{
    return max(1, (int) query('page', 1));
}

function per_page(): int
{
    return max(5, min(100, (int) query('per_page', app_config('items_per_page', 10))));
}

function status_badge(string $status): string
{
    $label = ucwords(str_replace('_', ' ', $status));
    $class = match ($status) {
        'active', 'approved', 'cleared', 'present' => 'badge-success',
        'inactive', 'rejected', 'not_cleared', 'absent' => 'badge-danger',
        'late' => 'badge-warning',
        default => 'badge-muted',
    };

    return '<span class="badge ' . $class . '">' . e($label) . '</span>';
}

function format_date(?string $date): string
{
    if (!$date) {
        return '-';
    }

    return date('M d, Y', strtotime($date));
}

function format_datetime(?string $date): string
{
    if (!$date) {
        return '-';
    }

    return date('M d, Y H:i', strtotime($date));
}

function letter_grade(float $score): string
{
    return match (true) {
        $score >= 70 => 'A',
        $score >= 60 => 'B',
        $score >= 50 => 'C',
        $score >= 40 => 'D',
        default => 'F',
    };
}

function upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_OK => 'Upload completed.',
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded file is too large.',
        UPLOAD_ERR_PARTIAL => 'The file uploaded only partially.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        default => 'The file upload failed.',
    };
}

// Aliases for consistency with camelCase naming
function formatDate(?string $date): string
{
    return format_date($date);
}

function formatDateTime(?string $date): string
{
    return format_datetime($date);
}
