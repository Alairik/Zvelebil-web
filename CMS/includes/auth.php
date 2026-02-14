<?php
/**
 * Authentication & session management
 */

function auth_start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => true,
            'cookie_samesite' => 'Lax',
        ]);
    }
}

function auth_login(string $username, string $password): bool {
    $db = db_connect();
    $stmt = $db->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? AND active = 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();

        // Update last login
        $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);
        return true;
    }
    return false;
}

function auth_logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function auth_check(): bool {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    if (time() - ($_SESSION['login_time'] ?? 0) > SESSION_LIFETIME) {
        auth_logout();
        return false;
    }
    return true;
}

function auth_require(): void {
    auth_start_session();
    if (!auth_check()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

function auth_require_admin(): void {
    auth_require();
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('Přístup odepřen. Tato akce vyžaduje oprávnění administrátora.');
    }
}

function auth_user(): ?array {
    if (!auth_check()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'],
    ];
}

function auth_is_admin(): bool {
    return ($_SESSION['role'] ?? '') === 'admin';
}
