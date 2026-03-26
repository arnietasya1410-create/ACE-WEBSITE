<?php

require_once __DIR__ . '/../security.php';
ace_set_security_headers();
ace_secure_session_start();
require_once __DIR__ . '/_inc.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

if (!ace_csrf_validate($_POST['csrf_token'] ?? '')) {
    $_SESSION['login_error'] = 'Invalid request. Please try again.';
    header('Location: login.php');
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!ace_rate_limit('login:' . $ip, 5, 300)) {
    $_SESSION['login_error'] = 'Too many attempts. Please wait a few minutes and try again.';
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'Please enter both username and password.';
    header('Location: login.php');
    exit;
}

// Check if admin exists
$stmt = $conn->prepare("SELECT admin_id, username, password_hash, role, is_locked, failed_attempts FROM admins WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    $_SESSION['login_error'] = 'Invalid username or password.';
    header('Location: login.php');
    exit;
}

// Check if account is locked
if ($admin['is_locked']) {
    $_SESSION['login_error'] = 'Your account has been locked due to multiple failed login attempts. Please contact a Super Admin to unlock it.';
    header('Location: login.php');
    exit;
}

// Verify password
if (password_verify($password, $admin['password_hash'])) {
    // Rotate session ID after successful login
    session_regenerate_id(true);

    // Upgrade hash if needed
    if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $rehash = $conn->prepare("UPDATE admins SET password_hash = ? WHERE admin_id = ?");
        if ($rehash) {
            $rehash->bind_param('si', $new_hash, $admin['admin_id']);
            $rehash->execute();
            $rehash->close();
        }
    }
    // Success - reset failed attempts and update last login
    $stmt = $conn->prepare("UPDATE admins SET failed_attempts = 0, last_login = NOW() WHERE admin_id = ?");
    $stmt->bind_param('i', $admin['admin_id']);
    $stmt->execute();
    $stmt->close();
    
    // Set session
    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['admin_user'] = $admin['username'];
    $_SESSION['admin_role'] = $admin['role'];
    
    // Redirect based on role
    if ($admin['role'] === 'super_admin') {
        header('Location: /ACE/admin/super_admin/dashboard.php');
    } else {
        header('Location: /ACE/admin/dashboard.php');
    }
    exit;
} else {
    // Failed login - increment failed attempts
    $failed_attempts = $admin['failed_attempts'] + 1;
    $is_locked = ($failed_attempts >= 3) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE admins SET failed_attempts = ?, is_locked = ?, locked_at = IF(?, NOW(), NULL) WHERE admin_id = ?");
    $stmt->bind_param('iiii', $failed_attempts, $is_locked, $is_locked, $admin['admin_id']);
    $stmt->execute();
    $stmt->close();
    
    if ($is_locked) {
        $_SESSION['login_error'] = 'Your account has been locked after 3 failed login attempts. Please contact a Super Admin.';
    } else {
        $remaining = 3 - $failed_attempts;
        $_SESSION['login_error'] = "Invalid password. You have $remaining attempt(s) remaining before your account is locked.";
    }
    
    header('Location: login.php');
    exit;
}
?>