<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

function session_start_safe(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('voting_sess');
        session_start();
    }
}

function is_logged_in(): bool {
    session_start_safe();
    return !empty($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

function current_user(): ?object {
    if (!is_logged_in()) return null;
    return queryOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function login(string $username, string $password): bool {
    $user = queryOne("SELECT * FROM users WHERE username = ? AND active = 1", [$username]);
    if (!$user) return false;
    if (!password_verify($password, $user->password)) return false;
    session_start_safe();
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user->id;
    $_SESSION['username']  = $user->username;
    return true;
}

function logout(): void {
    session_start_safe();
    session_destroy();
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

function csrf_token(): string {
    session_start_safe();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    session_start_safe();
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

function flash(string $key, string $msg = ''): string {
    session_start_safe();
    if ($msg) {
        $_SESSION['flash'][$key] = $msg;
        return '';
    }
    $val = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $val;
}
