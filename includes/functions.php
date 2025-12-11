<?php
// includes/functions.php

// Start session once for CSRF + flash messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    }
    return $_SESSION['csrf_token'];
}

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

