<?php
/**
 * Session-based authentication for the GMS admin panel (Phase 4).
 * Single hardcoded user. Swap for a users store / DB later.
 *
 *   Username: admin
 *   Password: gms2026   (change ADMIN_PASS_HASH below — see note)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const ADMIN_USER = 'admin';
// bcrypt hash of 'gms2026'. Regenerate with:  php -r "echo password_hash('NEW', PASSWORD_DEFAULT);"
const ADMIN_PASS_HASH = '$2y$10$UV.pDZrlMMXw5YVWPwAeM.4soOrtzuaGb74y1lrTdITU47GTyKK5S';

function admin_is_logged_in(): bool {
    return !empty($_SESSION['admin_authed']);
}

function admin_attempt_login(string $user, string $pass): bool {
    // constant-time username check + hashed password verify
    $user_ok = hash_equals(ADMIN_USER, $user);
    $pass_ok = password_verify($pass, ADMIN_PASS_HASH);
    if (!$user_ok || !$pass_ok) return false;

    session_regenerate_id(true);
    $_SESSION['admin_authed'] = true;
    $_SESSION['admin_user']   = $user;
    $_SESSION['admin_login']  = time();
    return true;
}

function admin_logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function admin_require_login(): void {
    if (!admin_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/* ---- CSRF helpers ---- */
function admin_csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function admin_csrf_check(?string $token): bool {
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
