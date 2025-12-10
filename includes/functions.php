<?php
// Shared helper functions for the app.

// Ensure session is available for CSRF + flash messages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function esc($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function get_int(array $source, string $key): ?int
{
    if (!isset($source[$key])) {
        return null;
    }
    $val = filter_var($source[$key], FILTER_VALIDATE_INT);
    return $val === false ? null : $val;
}

function get_string(array $source, string $key): ?string
{
    if (!isset($source[$key])) {
        return null;
    }
    $val = trim((string)$source[$key]);
    return $val === '' ? null : $val;
}

function get_email(array $source, string $key): ?string
{
    if (!isset($source[$key])) {
        return null;
    }
    $val = filter_var(trim((string)$source[$key]), FILTER_VALIDATE_EMAIL);
    return $val === false ? null : $val;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf(?string $token): void
{
    if (!$token || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(400);
        exit('Invalid CSRF token.');
    }
}

function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flashes'][$type][] = $message;
}

function get_flashes(): array
{
    $flashes = $_SESSION['flashes'] ?? [];
    unset($_SESSION['flashes']);
    return $flashes;
}
