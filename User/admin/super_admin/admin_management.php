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

    if ($action === 'update_admin' && $target_admin_id > 0) {
        $new_username = trim($_POST['username'] ?? '');
        $new_email = trim($_POST['email'] ?? '');
        $new_role = $_POST['role'] ?? 'admin';

        if ($new_username === '' || strlen($new_username) < 3) {
            $_SESSION['flash'] = 'Username must be at least 3 characters.';
            $_SESSION['flash_type'] = 'warning';
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = 'Please provide a valid email address.';
            $_SESSION['flash_type'] = 'warning';
        } else {
            $new_role = in_array($new_role, ['admin', 'super_admin'], true) ? $new_role : 'admin';

            $stmt = $conn->prepare("SELECT username, role FROM admins WHERE admin_id = ? LIMIT 1");
            $stmt->bind_param('i', $target_admin_id);
            $stmt->execute();
            $existing_result = $stmt->get_result();
            $existing_admin = $existing_result->fetch_assoc();
            $stmt->close();

            if (!$existing_admin) {
                $_SESSION['flash'] = 'Admin account not found.';
                $_SESSION['flash_type'] = 'danger';
            } else {
                if ($existing_admin['role'] === 'super_admin' && $new_role !== 'super_admin') {
                    $count_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM admins WHERE role = 'super_admin' AND admin_id != ?");
                    $count_stmt->bind_param('i', $target_admin_id);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();
                    $remaining_super = (int)($count_result->fetch_assoc()['cnt'] ?? 0);
                    $count_stmt->close();

                    if ($remaining_super < 1) {
                        $_SESSION['flash'] = 'At least one Super Admin must remain in the system.';
                        $_SESSION['flash_type'] = 'danger';
                        header('Location: admin_management.php');
                        exit;
                    }
                }

                $check_stmt = $conn->prepare("SELECT admin_id FROM admins WHERE (username = ? OR email = ?) AND admin_id != ? LIMIT 1");
                $check_stmt->bind_param('ssi', $new_username, $new_email, $target_admin_id);
                $check_stmt->execute();
                $dup_result = $check_stmt->get_result();
                $duplicate_exists = $dup_result->num_rows > 0;
                $check_stmt->close();

                if ($duplicate_exists) {
                    $_SESSION['flash'] = 'Username or email is already in use by another admin.';
                    $_SESSION['flash_type'] = 'danger';
                } else {
                    $update_stmt = $conn->prepare("UPDATE admins SET username = ?, email = ?, role = ? WHERE admin_id = ?");
                    $update_stmt->bind_param('sssi', $new_username, $new_email, $new_role, $target_admin_id);
                    $updated = $update_stmt->execute();
                    $update_stmt->close();

                    if ($updated) {
                        if ($target_admin_id === (int)($_SESSION['admin_id'] ?? 0)) {
                            $_SESSION['admin_user'] = $new_username;
                            $_SESSION['admin_role'] = $new_role;
                        }

                        if ($existing_admin['role'] !== $new_role) {
                            if ($new_role === 'super_admin') {
                                log_admin_promoted($new_username);
                            } else {
                                log_admin_demoted($new_username);
                            }
                        }

                        $_SESSION['flash'] = 'Admin details updated successfully.';
                        $_SESSION['flash_type'] = 'success';
                    } else {
                        $_SESSION['flash'] = 'Failed to update admin details.';
                        $_SESSION['flash_type'] = 'danger';
                    }
                }
            }
        }
    }

    if ($action === 'delete_admin' && $target_admin_id > 0) {
        if ($target_admin_id === (int)($_SESSION['admin_id'] ?? 0)) {
            $_SESSION['flash'] = 'You cannot delete your own account.';
            $_SESSION['flash_type'] = 'danger';
        } else {
            $stmt = $conn->prepare("SELECT username, role FROM admins WHERE admin_id = ? LIMIT 1");
            $stmt->bind_param('i', $target_admin_id);
            $stmt->execute();
            $target_result = $stmt->get_result();
            $target_admin = $target_result->fetch_assoc();
            $stmt->close();

            if (!$target_admin) {
                $_SESSION['flash'] = 'Admin account not found.';
                $_SESSION['flash_type'] = 'danger';
            } else {
                if ($target_admin['role'] === 'super_admin') {
                    $count_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM admins WHERE role = 'super_admin' AND admin_id != ?");
                    $count_stmt->bind_param('i', $target_admin_id);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();
                    $remaining_super = (int)($count_result->fetch_assoc()['cnt'] ?? 0);
                    $count_stmt->close();

                    if ($remaining_super < 1) {
                        $_SESSION['flash'] = 'Cannot delete the last Super Admin account.';
                        $_SESSION['flash_type'] = 'danger';
                        header('Location: admin_management.php');
                        exit;
                    }
                }

                $delete_stmt = $conn->prepare("DELETE FROM admins WHERE admin_id = ?");
                $delete_stmt->bind_param('i', $target_admin_id);
                $deleted = $delete_stmt->execute();
                $delete_stmt->close();

                if ($deleted) {
                    log_admin_deleted($target_admin['username']);
                    $_SESSION['flash'] = 'Admin account deleted successfully.';
                    $_SESSION['flash_type'] = 'warning';
                } else {
                    $_SESSION['flash'] = 'Failed to delete admin account.';
                    $_SESSION['flash_type'] = 'danger';
                }
            }
        }
    }
    
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
$stmt = $conn->query("SELECT admin_id, username, email, role, is_locked, failed_attempts, last_login, locked_at, created_at FROM admins ORDER BY role DESC, username ASC");
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
    <link rel="stylesheet" href="/front.css">
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
                                <th>Email</th>
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
                                <td><?= htmlspecialchars($admin['email'] ?? '') ?></td>
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
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-secondary" title="Edit Admin" data-bs-toggle="modal" data-bs-target="#editAdminModal<?= $admin['admin_id'] ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <?php if ($admin['admin_id'] !== $_SESSION['admin_id']): ?>
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
                                                <button type="submit" class="btn btn-info" title="Reset Password" onclick="return confirm('Generate a new temporary password for this admin?')">
                                                    <i class="bi bi-key"></i>
                                                </button>
                                            </form>

                                            <form method="POST" class="d-inline">
                                                <?= ace_csrf_input(); ?>
                                                <input type="hidden" name="action" value="delete_admin">
                                                <input type="hidden" name="admin_id" value="<?= $admin['admin_id'] ?>">
                                                <button type="submit" class="btn btn-danger" title="Delete Admin" onclick="return confirm('Delete this admin account permanently? This action cannot be undone.')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-light" disabled title="Current account">You</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php foreach ($admins as $admin): ?>
                <div class="modal fade" id="editAdminModal<?= $admin['admin_id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Admin: <?= htmlspecialchars($admin['username']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?= ace_csrf_input(); ?>
                                    <input type="hidden" name="action" value="update_admin">
                                    <input type="hidden" name="admin_id" value="<?= $admin['admin_id'] ?>">

                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" minlength="3" required value="<?= htmlspecialchars($admin['username']) ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($admin['email'] ?? '') ?>">
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label">Role</label>
                                        <select name="role" class="form-select" required>
                                            <option value="admin" <?= $admin['role'] === 'admin' ? 'selected' : '' ?>>Regular Admin</option>
                                            <option value="super_admin" <?= $admin['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
