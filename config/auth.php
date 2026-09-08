<?php
// config/auth.php
require_once 'db.php';

// Secure Session Configuration
// This must be called BEFORE session_start()
ini_set('session.cookie_httponly', 1); // Prevent JavaScript from accessing the session cookie (XSS mitigation)
ini_set('session.use_only_cookies', 1); // Prevent passing session ID via URL
ini_set('session.cookie_samesite', 'Strict'); // Mitigate CSRF

session_start();

/**
 * Check if the user is currently logged in.
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an admin.
 * @return bool
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Middleware: Redirects unauthenticated users to the login page.
 * Place at the top of protected pages (like index.php).
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Middleware: Redirects non-admins to the main application.
 * Place at the top of admin-only pages.
 */
function require_admin() {
    require_login(); // Must be logged in first
    if (!is_admin()) {
        header('Location: ../index.php'); // Assuming admin pages are in /admin/
        exit;
    }
}

/**
 * Logs a user in by setting their session variables.
 * Regenerates the session ID to prevent session fixation attacks.
 * 
 * @param array $user The user record from the database
 */
function login_user($user) {
    // Prevent session fixation
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
}

/**
 * Logs out the current user and securely destroys the session.
 */
function logout_user() {
    // Unset all of the session variables
    $_SESSION = array();

    // Kill the session, also delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
?>
