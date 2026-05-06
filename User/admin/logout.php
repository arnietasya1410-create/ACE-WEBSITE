<?php

require_once __DIR__ . '/../security.php';
ace_set_security_headers();
ace_secure_session_start();
require_once __DIR__ . '/_inc.php';

// Remove admin-related session data but keep session for flash message
$adminKeys = ['admin_id', 'admin_username', 'admin_user', 'admin_role', 'admin_email', 'is_admin'];
foreach ($adminKeys as $k) {
    if (isset($_SESSION[$k])) unset($_SESSION[$k]);
}

// Clear any "remember me" or custom auth cookies (common names)
$cookieNames = ['remember_token', 'ACE_admin', 'admin_id'];
foreach ($cookieNames as $cn) {
    if (isset($_COOKIE[$cn])) {
        setcookie($cn, '', time() - 3600, '/');
        unset($_COOKIE[$cn]);
    }
}

// Regenerate session id to reduce fixation risk
session_regenerate_id(true);

// Set a flash message (uses flash_set if available)
if (function_exists('flash_set')) {
    flash_set('You have been logged out.');
} else {
    $_SESSION['flash'] = 'You have been logged out.';
}

// AUTO-LOG LOGOUT
log_logout();

session_destroy();
header('Location: login.php');
exit;
?>