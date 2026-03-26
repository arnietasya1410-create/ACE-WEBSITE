<?php
require_once __DIR__ . '/_inc.php';
require_admin();

$admin_username = $_SESSION['admin_user'] ?? 'Admin';
$flash = function_exists('flash_get') ? flash_get() : null;
$records = [];
$errorMessage = null;

if (!isset($conn) || !($conn instanceof mysqli)) {
    $errorMessage = 'Database is not available.';
} else {
    $tableCheck = $conn->query("SHOW TABLES LIKE 'cost_calculator_records'");
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        $errorMessage = 'Table cost_calculator_records is missing. Run admin/sql/cost_calculator_tables.sql first.';
    } else {
        $q = $conn->query("SELECT id, calc_name, created_by_username, participants, suggested_fee, subtotal_after_hrd_charges, created_at FROM cost_calculator_records ORDER BY created_at DESC, id DESC");
        if ($q) {
            while ($row = $q->fetch_assoc()) {
                $records[] = $row;
            }
        }

    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Cost Calculator Records — ACE Admin</title>
  <link rel="stylesheet" href="/ACE/User/front.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php
if (is_super_admin()) {
    require_once __DIR__ . '/partials/header_super.php';
} else {
    require_once __DIR__ . '/partials/header.php';
}
?>

<section class="section-card" style="margin: 12px auto; max-width: 1200px;">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-1">Saved Cost Calculators</h4>
        <small class="text-muted">Viewer: <?= htmlspecialchars($admin_username) ?></small>
      </div>
      <a href="/ACE/admin/directcost.php" class="btn btn-accent btn-sm">Open Cost Calculator</a>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-info"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
      <div class="alert alert-warning"><?= htmlspecialchars($errorMessage) ?></div>
    <?php elseif (empty($records)): ?>
      <div class="alert alert-secondary">No saved records yet.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Calculator Name</th>
              <th>Created By</th>
              <th>Participants</th>
              <th>Suggested Fee (RM)</th>
              <th>Subtotal After HRD (RM)</th>
              <th>Date & Time</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($records as $record): ?>
              <tr>
                <td><?= (int)$record['id'] ?></td>
                <td><?= htmlspecialchars($record['calc_name']) ?></td>
                <td><?= htmlspecialchars($record['created_by_username']) ?></td>
                <td><?= htmlspecialchars(number_format((float)$record['participants'], 2)) ?></td>
                <td><?= htmlspecialchars(number_format((float)$record['suggested_fee'], 2)) ?></td>
                <td><?= htmlspecialchars(number_format((float)$record['subtotal_after_hrd_charges'], 2)) ?></td>
                <td><?= htmlspecialchars($record['created_at']) ?></td>
                <td>
                  <a class="btn btn-outline-primary btn-sm" href="/ACE/admin/cost_calculator_view.php?id=<?= (int)$record['id'] ?>">View</a>
                  <a class="btn btn-outline-warning btn-sm" href="/ACE/admin/directcost.php?edit_id=<?= (int)$record['id'] ?>">Edit</a>
                  <?php $isOwner = (($record['created_by_username'] ?? '') === $admin_username); ?>
                  <?php if ($isOwner): ?>
                    <form method="post" action="/ACE/admin/cost_calculator_delete.php" class="d-inline" onsubmit="return confirm('Delete this calculator record?');">
                      <?= ace_csrf_input(); ?>
                      <input type="hidden" name="record_id" value="<?= (int)$record['id'] ?>">
                      <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                    </form>
                  <?php else: ?>
                    <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Only creator can delete">Delete</button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
