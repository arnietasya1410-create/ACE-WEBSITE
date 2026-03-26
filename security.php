<?php
// Central security helpers (sessions, CSRF, headers).

function ace_set_security_headers() {
    if (headers_sent()) {
        return;
    }

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('X-Permitted-Cross-Domain-Policies: none');

    // Conservative CSP tuned for current CDN usage and inline scripts.
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.instagram.com https://www.tiktok.com; " .
           "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; " .
           "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; " .
           "img-src 'self' data: https:; " .
           "connect-src 'self' https:; " .
           "frame-src https://www.youtube.com https://www.tiktok.com https://www.instagram.com https://www.facebook.com;";
    header('Content-Security-Policy: ' . $csp);
}

function ace_secure_session_start() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function ace_require_https() {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return;
    }

    if (getenv('ACE_FORCE_HTTPS') !== '1') {
        return;
    }

    $host = $_SERVER['HTTP_HOST'] ?? '';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if ($host) {
        header('Location: https://' . $host . $uri, true, 301);
        exit;
    }
}

function ace_csrf_token() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ace_secure_session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function ace_csrf_input() {
    $token = ace_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function ace_csrf_validate($token) {
    $session = $_SESSION['csrf_token'] ?? '';
    if (!$token || !$session) {
        return false;
    }

    return hash_equals($session, $token);
}

function ace_csrf_rotate() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function ace_rate_limit($key, $limit, $window_seconds) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ace_secure_session_start();
    }

    $now = time();
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }

    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [];
    }

    $window_start = $now - $window_seconds;
    $attempts = array_filter(
        $_SESSION['rate_limits'][$key],
        function ($ts) use ($window_start) {
            return $ts >= $window_start;
        }
    );

    if (count($attempts) >= $limit) {
        $_SESSION['rate_limits'][$key] = array_values($attempts);
        return false;
    }

    $attempts[] = $now;
    $_SESSION['rate_limits'][$key] = array_values($attempts);
    return true;
}

function ace_validate_url_or_empty($url) {
    if ($url === null || $url === '') {
        return true;
    }

    return (bool)filter_var($url, FILTER_VALIDATE_URL);
}

function ace_validate_image_upload(array $file) {
    if (!isset($file['tmp_name'], $file['name'], $file['error'], $file['size'])) {
        return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];

    if (!isset($allowed[$ext])) {
        return false;
    }

    $mime = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
    } elseif (function_exists('mime_content_type')) {
        $mime = mime_content_type($file['tmp_name']);
    }

    if ($mime) {
        if ($mime !== $allowed[$ext]) {
            return false;
        }
    } else {
        $img = @getimagesize($file['tmp_name']);
        if ($img === false) {
            return false;
        }
    }

    return true;
}
?>