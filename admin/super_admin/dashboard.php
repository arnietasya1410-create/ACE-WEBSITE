<?php
require_once __DIR__ . '/../_inc.php';
require_super_admin();

$admin_username = $_SESSION['admin_user'] ?? 'Super Admin';
$page_title = 'Super Admin Dashboard';


// Get statistics
$total_admins = $conn->query("SELECT COUNT(*) as count FROM admins")->fetch_assoc()['count'];
$locked_admins = $conn->query("SELECT COUNT(*) as count FROM admins WHERE is_locked = 1")->fetch_assoc()['count'];
$total_programmes = $conn->query("SELECT COUNT(*) as count FROM programmes")->fetch_assoc()['count'];

// Recent admin activity
$recent_logins = [];
$stmt = $conn->query("SELECT username, last_login, role FROM admins WHERE last_login IS NOT NULL ORDER BY last_login DESC LIMIT 10");
while ($row = $stmt->fetch_assoc()) {
    $recent_logins[] = $row;
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
</head>
<body>

<?php require_once __DIR__ . '/../partials/header_super.php'; ?>

      <div class="ms-3">
        <div class="d-flex align-items-center gap-2">
          <span style="font-weight:600;color:var(--accent)">🛡️ ACE — <?= htmlspecialchars($admin_username) ?> (Super Admin)</span>
        </div>
        <small class="text-muted">Super Admin Console</small>
      </div>

<section class="section-card">
    <div class="container">
        <h2 class="mb-4">🛡️ Super Admin Dashboard</h2>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4 justify-content-center">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                        <h3 class="mt-2"><?= $total_admins ?></h3>
                        <p class="text-muted mb-0">Total Admins</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-lock text-danger" style="font-size: 2rem;"></i>
                        <h3 class="mt-2"><?= $locked_admins ?></h3>
                        <p class="text-muted mb-0">Locked Accounts</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-calendar-event text-success" style="font-size: 2rem;"></i>
                        <h3 class="mt-2"><?= $total_programmes ?></h3>
                        <p class="text-muted mb-0">Total Programmes</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">⚡ Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="/ACE/admin/super_admin/admin_management.php" class="btn btn-outline-primary w-100">
                            <i class="bi bi-people-fill"></i> Manage Admins
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="/ACE/admin/super_admin/activity_logs.php" class="btn btn-outline-info w-100">
                            <i class="bi bi-clock-history"></i> Activity Logs
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="/ACE/admin/program_list.php" class="btn btn-outline-success w-100">
                            <i class="bi bi-calendar-check"></i> Manage Programmes
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="/ACE/admin/social_media.php" class="btn btn-outline-success w-100">
                            <i class="bi bi-share"></i> Manage Social Media
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="/ACE/admin/directcost.php" class="btn btn-outline-warning w-100">
                            <i class="bi bi-calculator"></i> Cost Calculator
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Admin Activity -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📊 Recent Admin Activity</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logins as $login): ?>
                            <tr>
                                <td><?= htmlspecialchars($login['username']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $login['role'] === 'super_admin' ? 'danger' : 'primary' ?>">
                                        <?= $login['role'] === 'super_admin' ? '🛡️ Super Admin' : '👤 Admin' ?>
                                    </span>
                                </td>
                                <td><?= $login['last_login'] ? date('d M Y, H:i A', strtotime($login['last_login'])) : 'Never' ?></td>
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