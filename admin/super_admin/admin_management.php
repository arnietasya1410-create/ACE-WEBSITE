<?php

require_once __DIR__ . '/../_inc.php';
require_super_admin();

$admin_username = $_SESSION['admin_user'] ?? 'Super Admin';
$page_title = 'Admin Management';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ace_csrf_validate($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash'] = 'Invalid request.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: admin_management.php');
        exit;
    }
    $action = $_POST['action'] ?? '';
    $target_admin_id = (int)($_POST['admin_id'] ?? 0);
    
    if ($action === 'unlock' && $target_admin_id > 0) {
        $stmt = $conn->prepare("UPDATE admins SET is_locked = 0, failed_attempts = 0, locked_at = NULL WHERE admin_id = ?");
        $stmt->bind_param('i', $target_admin_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash'] = 'Admin account unlocked successfully.';
        $_SESSION['flash_type'] = 'success';
    }
    
    if ($action === 'lock' && $target_admin_id > 0) {
        $stmt = $conn->prepare("UPDATE admins SET is_locked = 1, locked_at = NOW() WHERE admin_id = ?");
        $stmt->bind_param('i', $target_admin_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash'] = 'Admin account locked successfully.';
        $_SESSION['flash_type'] = 'warning';
    }
    
    if ($action === 'reset_password' && $target_admin_id > 0) {
        // Generate a random password
        $new_password = bin2hex(random_bytes(4)); // Generates 8-character random password
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Get admin username for logging
        $stmt = $conn->prepare("SELECT username FROM admins WHERE admin_id = ?");
        $stmt->bind_param('i', $target_admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $target_admin = $result->fetch_assoc();
        $stmt->close();
        
        // Update password
        $stmt = $conn->prepare("UPDATE admins SET password_hash = ?, failed_attempts = 0, is_locked = 0, locked_at = NULL WHERE admin_id = ?");
        $stmt->bind_param('si', $hashed, $target_admin_id);
        $stmt->execute();
        $stmt->close();
        
        // Log the password reset
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_username, action_type, action_description, target_type, target_id, ip_address) VALUES (?, 'password_reset', ?, 'admin', ?, ?)");
        $log_description = "Password reset for admin: " . $target_admin['username'];
        $current_admin_username = $_SESSION['admin_user'];
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $log_stmt->bind_param('ssis', $current_admin_username, $log_description, $target_admin_id, $ip_address);
        $log_stmt->execute();
        $log_stmt->close();
        
        $_SESSION['flash'] = "Password reset successfully for <strong>{$target_admin['username']}</strong>. New password: <code class='bg-dark text-warning p-1 rounded'>$new_password</code> (Please save this, it won't be shown again)";
        $_SESSION['flash_type'] = 'info';
    }
    
    header('Location: admin_management.php');
    exit;
}

// Get all admins
$admins = [];
$stmt = $conn->query("SELECT admin_id, username, role, is_locked, failed_attempts, last_login, locked_at, created_at FROM admins ORDER BY role DESC, username ASC");
while ($row = $stmt->fetch_assoc()) {
    $admins[] = $row;
}

$flash = $_SESSION['flash'] ?? null;
$flash_type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash'], $_SESSION['flash_type']);
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
</head>
<body>

<?php require_once __DIR__ . '/../partials/header_super.php'; ?>

<section class="section-card">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>👥 Admin Management</h2>
            <a href="register.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Admin
            </a>
        </div>
        
        <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash_type) ?> alert-dismissible fade show">
            <?= $flash ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Failed Attempts</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($admin['username']) ?></strong></td>
                                <td>
                                    <span class="badge bg-<?= $admin['role'] === 'super_admin' ? 'danger' : 'primary' ?>">
                                        <?= $admin['role'] === 'super_admin' ? '🛡️ Super Admin' : '👤 Admin' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($admin['is_locked']): ?>
                                        <span class="badge bg-danger">🔒 Locked</span>
                                        <br><small class="text-muted"><?= date('d M Y H:i', strtotime($admin['locked_at'])) ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-success">✅ Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($admin['failed_attempts'] > 0): ?>
                                        <span class="badge bg-warning text-dark"><?= $admin['failed_attempts'] ?> / 3</span>
                                    <?php else: ?>
                                        <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $admin['last_login'] ? date('d M Y, H:i A', strtotime($admin['last_login'])) : '<span class="text-muted">Never</span>' ?>
                                </td>
                                <td>
                                    <?php if ($admin['admin_id'] !== $_SESSION['admin_id']): ?>
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($admin['is_locked']): ?>
                                                <form method="POST" class="d-inline">
                                                    <?= ace_csrf_input(); ?>
                                                    <input type="hidden" name="action" value="unlock">
                                                    <input type="hidden" name="admin_id" value="<?= $admin['admin_id'] ?>">
                                                    <button type="submit" class="btn btn-success" title="Unlock Account">
                                                        <i class="bi bi-unlock"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" class="d-inline">
                                                    <?= ace_csrf_input(); ?>
                                                    <input type="hidden" name="action" value="lock">
                                                    <input type="hidden" name="admin_id" value="<?= $admin['admin_id'] ?>">
                                                    <button type="submit" class="btn btn-warning" title="Lock Account" onclick="return confirm('Lock this admin account?')">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" class="d-inline">
                                                <?= ace_csrf_input(); ?>
                                                <input type="hidden" name="action" value="reset_password">
                                                <input type="hidden" name="admin_id" value="<?= $admin['admin_id'] ?>">
                                                <button type="submit" class="btn btn-info" title="Reset Password" onclick="return confirm('Reset password to default (admin123)?')">
                                                    <i class="bi bi-key"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">You</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>