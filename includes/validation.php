<?php

function validate_required(array $data, array $fields): array
{
    $errors = [];
    foreach ($fields as $field => $label) {
        if (trim((string) ($data[$field] ?? '')) === '') {
            $errors[$field] = $label . ' is required.';
        }
    }
    return $errors;
}

function validate_email_field(array $data, string $field = 'email'): array
{
    $email = trim((string) ($data[$field] ?? ''));
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [$field => 'Enter a valid email address.'];
    }
    return [];
}

function validate_in(array $data, string $field, array $allowed, string $label): array
{
    if (!in_array((string) ($data[$field] ?? ''), $allowed, true)) {
        return [$field => $label . ' is invalid.'];
    }
    return [];
}

function validate_number_range(array $data, string $field, float $min, float $max, string $label): array
{
    $value = $data[$field] ?? null;
    if (!is_numeric($value) || (float) $value < $min || (float) $value > $max) {
        return [$field => $label . " must be between {$min} and {$max}."];
    }
    return [];
}
