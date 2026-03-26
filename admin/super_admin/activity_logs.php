<?php

require_once __DIR__ . '/../_inc.php';
require_admin();

// Only super admins can view logs
if (!is_super_admin()) {
    header('Location: ../dashboard.php');
    exit;
}

$admin_username = $_SESSION['admin_user'] ?? 'Admin';
$page_title = 'Activity Logs';

// Filters
$filter_admin = isset($_GET['admin']) ? trim($_GET['admin']) : '';
$filter_action = isset($_GET['action']) ? trim($_GET['action']) : '';
$filter_date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$filter_date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = [];
$params = [];
$types = '';

if ($filter_admin !== '') {
    $whereConditions[] = "admin_username = ?";
    $params[] = $filter_admin;
    $types .= 's';
}

if ($filter_action !== '') {
    $whereConditions[] = "action_type = ?";
    $params[] = $filter_action;
    $types .= 's';
}

if ($filter_date_from !== '') {
    $whereConditions[] = "DATE(created_at) >= ?";
    $params[] = $filter_date_from;
    $types .= 's';
}

if ($filter_date_to !== '') {
    $whereConditions[] = "DATE(created_at) <= ?";
    $params[] = $filter_date_to;
    $types .= 's';
}

$whereSQL = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Count total
$countQuery = "SELECT COUNT(*) as total FROM activity_logs $whereSQL";
if (!empty($params)) {
    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalRecords = $result->fetch_assoc()['total'];
    $stmt->close();
} else {
    $totalRecords = $conn->query($countQuery)->fetch_assoc()['total'];
}

$totalPages = ceil($totalRecords / $perPage);

// Fetch logs
$query = "
    SELECT * FROM activity_logs 
    $whereSQL 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
";

$paramsCopy = $params;
$paramsCopy[] = $perPage;
$paramsCopy[] = $offset;
$typesCopy = $types . 'ii';

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($typesCopy, ...$paramsCopy);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}
$stmt->close();

// Get all admins for filter
$admins = [];
$q = $conn->query("SELECT DISTINCT admin_username FROM activity_logs ORDER BY admin_username");
while ($row = $q->fetch_assoc()) {
    $admins[] = $row['admin_username'];
}

// Action types for filter
$action_types = [
    'authentication' => 'Authentication',
    'programme' => 'Programme',
    'application' => 'Application',
    'query' => 'Query',
    'admin_management' => 'Admin Management',
    'settings' => 'Settings'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> — ACE Admin</title>
    <link rel="stylesheet" href="/ACE/User/front.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --accent: #6f42c1;
        }
        
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .section-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin: 30px auto;
            max-width: 1400px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .log-table {
            font-size: 0.9rem;
        }
        
        .log-table th {
            background: var(--accent);
            color: white;
            font-weight: 600;
            padding: 12px;
            white-space: nowrap;
        }
        
        .log-table td {
            padding: 10px;
            vertical-align: middle;
        }
        
        .badge-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .action-authentication { background: #e3f2fd; color: #1976d2; }
        .action-programme { background: #f3e5f5; color: #7b1fa2; }
        .action-application { background: #fff3e0; color: #f57c00; }
        .action-query { background: #e8f5e9; color: #388e3c; }
        .action-admin_management { background: #ffebee; color: #c62828; }
        .action-settings { background: #f5f5f5; color: #616161; }
        
        .filter-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .pagination .page-link {
            color: var(--accent);
        }
        
        .pagination .page-item.active .page-link {
            background: var(--accent);
            border-color: var(--accent);
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../partials/header_super.php'; ?>

<section class="section-card">
    <h2 style="color: var(--accent); margin-bottom: 25px;">
        <i class="bi bi-clock-history"></i> Activity Logs
    </h2>
    
    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label"><strong>Admin</strong></label>
                <select name="admin" class="form-select">
                    <option value="">All Admins</option>
                    <?php foreach ($admins as $admin): ?>
                        <option value="<?= htmlspecialchars($admin) ?>" <?= $filter_admin === $admin ? 'selected' : '' ?>>
                            <?= htmlspecialchars($admin) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label"><strong>Action Type</strong></label>
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <?php foreach ($action_types as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $filter_action === $value ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><strong>Date From</strong></label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filter_date_from) ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><strong>Date To</strong></label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filter_date_to) ?>">
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
        
        <?php if ($filter_admin || $filter_action || $filter_date_from || $filter_date_to): ?>
            <div class="mt-2">
                <a href="activity_logs.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Stats -->
    <div class="alert alert-info">
        <strong><?= number_format($totalRecords) ?></strong> log entries found
    </div>
    
    <!-- Logs Table -->
    <div class="table-responsive">
        <table class="table table-hover log-table">
            <thead>
                <tr>
                    <th style="width: 140px;">Date & Time</th>
                    <th style="width: 120px;">Admin</th>
                    <th style="width: 150px;">Action Type</th>
                    <th>Description</th>
                    <th style="width: 120px;">IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i><br>
                            No activity logs found
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <small class="text-muted">
                                    <?= date('M d, Y', strtotime($log['created_at'])) ?><br>
                                    <?= date('h:i A', strtotime($log['created_at'])) ?>
                                </small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($log['admin_username']) ?></strong>
                            </td>
                            <td>
                                <span class="badge-action action-<?= htmlspecialchars($log['action_type']) ?>">
                                    <?= $action_types[$log['action_type']] ?? htmlspecialchars($log['action_type']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($log['action_description']) ?></td>
                            <td><code><?= htmlspecialchars($log['ip_address']) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&admin=<?= urlencode($filter_admin) ?>&action=<?= urlencode($filter_action) ?>&date_from=<?= urlencode($filter_date_from) ?>&date_to=<?= urlencode($filter_date_to) ?>">
                            Previous
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&admin=<?= urlencode($filter_admin) ?>&action=<?= urlencode($filter_action) ?>&date_from=<?= urlencode($filter_date_from) ?>&date_to=<?= urlencode($filter_date_to) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&admin=<?= urlencode($filter_admin) ?>&action=<?= urlencode($filter_action) ?>&date_from=<?= urlencode($filter_date_from) ?>&date_to=<?= urlencode($filter_date_to) ?>">
                            Next
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>