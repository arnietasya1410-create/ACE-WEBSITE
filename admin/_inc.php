<?php
// Common admin include: session, DB loader (ACE/khun.php), auth helpers, small utilities.
require_once __DIR__ . '/../security.php';
ace_set_security_headers();
ace_secure_session_start();
ace_require_https();

// load DB connector from ACE root (one level up)
$khunPath = realpath(__DIR__ . '/../khun.php');
if ($khunPath && file_exists($khunPath)) require_once $khunPath;

// flash helpers
if (!function_exists('flash_set')) {
    function flash_set($msg){ $_SESSION['flash'] = $msg; }
}
if (!function_exists('flash_get')) {
    $m = $_SESSION['flash'] ?? null;
    function flash_get(){ $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m; }
}

// require admin session
function require_admin() {
    // Don't redirect if we're already on login page or processing login
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page === 'login.php' || $current_page === 'login_process.php') {
        return;
    }
    
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_user'])) {
        header('Location: /ACE/admin/login.php');
        exit;
    }
}

function require_super_admin() {
    // Don't redirect if we're already on login page or processing login
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page === 'login.php' || $current_page === 'login_process.php') {
        return;
    }
    
    require_admin();
    
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'super_admin') {
        http_response_code(403);
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Access Denied</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container mt-5">
                <div class="alert alert-danger">
                    <h4>⛔ Access Denied</h4>
                    <p>You do not have permission to access this page. Super Admin privileges required.</p>
                    <a href="/ACE/admin/dashboard.php" class="btn btn-primary">Return to Dashboard</a>
                </div>
            </div>
        </body>
        </html>';
        exit;
    }
}

function is_super_admin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

// small slug helper
function slugify($s){
    $s = trim(mb_strtolower((string)$s));
    $s = preg_replace('/[^\p{L}\p{N}]+/u','-',$s);
    $s = trim($s,'-');
    return $s ?: bin2hex(random_bytes(4));
}

function check_login() {
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

require_once __DIR__ . '/log_activity.php';
?>