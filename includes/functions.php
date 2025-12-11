<?php
<<<<<<< HEAD
// Shared helper functions for the app.

// Ensure session is available for CSRF + flash messages.
=======
// includes/functions.php

// Start session once for CSRF + flash messages
>>>>>>> 35c37ff1b165e0c72cba96d85d82eb9478467533
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<<<<<<< HEAD
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
=======
/**
 * Escape output for HTML.
 */
function esc(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Safe int from array (e.g., $_GET / $_POST).
 */
function get_int(array $src, string $key): ?int {
    if (!isset($src[$key]) || !is_numeric($src[$key])) {
        return null;
    }
    return (int)$src[$key];
}

/**
 * Safe string from array.
 */
function get_string(array $src, string $key, bool $trim = true): ?string {
    if (!isset($src[$key])) {
        return null;
    }
    $val = (string)$src[$key];
    if ($trim) {
        $val = trim($val);
    }
    return $val === '' ? null : $val;
}

/**
 * Email validator.
 */
function get_email(array $src, string $key): ?string {
    $val = get_string($src, $key);
    if ($val === null) {
        return null;
    }
    return filter_var($val, FILTER_VALIDATE_EMAIL) ?: null;
}

/**
 * CSRF token generator.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
>>>>>>> 35c37ff1b165e0c72cba96d85d82eb9478467533
    }
    return $_SESSION['csrf_token'];
}

<<<<<<< HEAD
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
=======
/**
 * CSRF check for POST requests.
 */
function check_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
        http_response_code(400);
        echo "Invalid CSRF token.";
        exit;
    }
}

/**
 * Simple redirect helper.
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

>>>>>>> 35c37ff1b165e0c72cba96d85d82eb9478467533
