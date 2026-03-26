<?php
require_once __DIR__ . '/../../security.php';
ace_set_security_headers();
ace_secure_session_start();
require_once __DIR__ . "/secret_key.php";

/* ============================================================
   ULTRA SECURE PASSWORD GATE (AES + Always-Locked + Logout)
   ============================================================ */

// LOG OUT: "End Session" button click
if (isset($_POST['logout'])) {
    unset($_SESSION['access_granted']);
    header("Location: register.php");
    exit;
}

// If no access granted or password is incorrect
if (!isset($_SESSION['access_granted'])) {

    // If user submitted a password
    if (isset($_POST['access'])) {
        if (!ace_csrf_validate($_POST['csrf_token'] ?? '')) {
            $error_msg = "Invalid request. Please try again.";
        } else {
            $input_pw = $_POST['access'] ?? '';
        
        // Decrypt using YOUR method from secret_key.php
        $decrypted = openssl_decrypt(
            $encryptedPassword,
            'AES-256-CBC',
            $key,
            0,
            substr($key, 0, 16)
        );

            if ($input_pw === $decrypted) {
                $_SESSION['access_granted'] = true;
                header("Location: register.php");
                exit;
            } else {
                $error_msg = "❌ Incorrect password. Access denied.";
            }
        }
    }

    // Still locked? Show password page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>🔐 Access Gate</title>
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .lock-box {
                background: white;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                text-align: center;
                max-width: 400px;
                width: 90%;
            }
            .lock-icon {
                font-size: 64px;
                margin-bottom: 20px;
                animation: pulse 2s infinite;
            }
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
            h2 {
                color: #333;
                margin-bottom: 10px;
            }
            p {
                color: #666;
                margin-bottom: 20px;
            }
            input[type="password"] {
                width: 100%;
                padding: 12px;
                border: 2px solid #ddd;
                border-radius: 6px;
                font-size: 16px;
                margin-bottom: 15px;
                transition: border-color 0.3s;
            }
            input[type="password"]:focus {
                outline: none;
                border-color: #667eea;
            }
            button {
                width: 100%;
                padding: 12px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 6px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                transition: transform 0.2s;
            }
            button:hover {
                transform: translateY(-2px);
            }
            .error {
                color: #e74c3c;
                margin-top: 15px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="lock-box">
            <div class="lock-icon">🔐</div>
            <h2>Access Restricted</h2>
            <p>Enter the master password to continue</p>
            <form method="POST">
                <?= ace_csrf_input(); ?>
                <input type="password" name="access" placeholder="Enter password" required autofocus>
                <button type="submit">🔓 Unlock</button>
            </form>
            <?php if (isset($error_msg)): ?>
                <div class="error"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ===== PASSWORD VERIFIED - Continue with the page =====
?>
<?php
require_once __DIR__ . '/../_inc.php';
require_super_admin();

$admin_username = $_SESSION['admin_user'] ?? 'Super Admin';
$page_title = 'Register New Admin';

// Flash helpers
if (!function_exists('flash_set')) {
    function flash_set($msg, $type = 'success'){ 
        $_SESSION['flash'] = $msg; 
        $_SESSION['flash_type'] = $type;
    }
}
if (!function_exists('flash_get')) {
    function flash_get(){ 
        $msg = $_SESSION['flash'] ?? null; 
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash'], $_SESSION['flash_type']); 
        return ['msg' => $msg, 'type' => $type];
    }
}

// Get flash message BEFORE any output
$flash_data = flash_get();
$flash_msg = $flash_data['msg'] ?? null;
$flash_type = $flash_data['type'] ?? 'success';

// CSRF token
$csrf = ace_csrf_token();

$success = false;

// Handle POST registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!ace_csrf_validate($_POST['csrf'] ?? '')) {
        flash_set('Invalid request.', 'danger');
        header('Location: register.php');
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = $_POST['role'] ?? 'admin';

    // Validate input
    if (empty($username) || strlen($username) < 3) {
        flash_set('Username must be at least 3 characters.', 'warning');
        header('Location: register.php');
        exit;
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('Please enter a valid email address.', 'warning');
        header('Location: register.php');
        exit;
    }
    if (empty($password) || strlen($password) < 6) {
        flash_set('Password must be at least 6 characters.', 'warning');
        header('Location: register.php');
        exit;
    }
    if ($password !== $password_confirm) {
        flash_set('Passwords do not match.', 'warning');
        header('Location: register.php');
        exit;
    }

    // Check if username or email already exists
    $check = $conn->prepare("SELECT admin_id FROM admins WHERE username = ? OR email = ? LIMIT 1");
    $check->bind_param('ss', $username, $email);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows > 0) {
        flash_set('Username or email already registered.', 'danger');
        $check->close();
        header('Location: register.php');
        exit;
    }
    $check->close();

    // Hash password and insert
    $pwd_hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO admins (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $username, $email, $pwd_hash, $role);
    
    if ($stmt->execute()) {
        $success = true;
        $is_super = ($role === 'super_admin');
        
        // LOG THE ACTIVITY
        log_admin_created($username, $is_super);
        
        flash_set("✅ Admin account created successfully! Username: <strong>$username</strong>", 'success');
        ace_csrf_rotate();
    } else {
        flash_set('❌ Registration failed. Please try again.', 'danger');
    }
    $stmt->close();

    header('Location: register.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — ACE</title>
    <link rel="stylesheet" href="/ACE/User/front.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .register-card {
            max-width: 500px;
            margin: 50px auto;
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../partials/header_super.php'; ?>

<section class="section-card">
    <div class="container">
        <div class="register-card">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h4><i class="bi bi-person-plus-fill"></i> Register New Admin</h4>
                        <small class="text-muted">Create a new admin account</small>
                    </div>

                    <?php if ($flash_msg): ?>
                        <div class="alert alert-<?= htmlspecialchars($flash_type) ?> alert-dismissible fade show" role="alert">
                            <?= $flash_msg ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input name="username" type="text" class="form-control" placeholder="Min 3 characters" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input name="email" type="email" class="form-control" placeholder="admin@example.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role *</label>
                            <select name="role" class="form-select" required>
                                <option value="admin">Regular Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                            <small class="text-muted">Super admins have full system access</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input name="password" type="password" class="form-control" placeholder="Min 6 characters" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password *</label>
                            <input name="password_confirm" type="password" class="form-control" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-check-circle"></i> Create Admin Account
                            </button>
                            <a href="admin_management.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Admin Management
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>