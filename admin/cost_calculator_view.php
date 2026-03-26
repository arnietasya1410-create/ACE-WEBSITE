<?php
require_once __DIR__ . '/_inc.php';
require_admin();

$admin_username = $_SESSION['admin_user'] ?? 'Admin';
$recordId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$record = null;
$errorMessage = null;

if ($recordId <= 0) {
    $errorMessage = 'Invalid record ID.';
} elseif (!isset($conn) || !($conn instanceof mysqli)) {
    $errorMessage = 'Database is not available.';
} else {
    $tableCheck = $conn->query("SHOW TABLES LIKE 'cost_calculator_records'");
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        $errorMessage = 'Table cost_calculator_records is missing. Run admin/sql/cost_calculator_tables.sql first.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM cost_calculator_records WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $recordId);
            $stmt->execute();
            $res = $stmt->get_result();
            $record = $res ? $res->fetch_assoc() : null;
            $stmt->close();
        }

        if (!$record) {
            $errorMessage = 'Record not found.';
        }
    }
}

$details = [];
$summary = [];
if ($record) {
    $details = json_decode((string)($record['calculation_payload'] ?? ''), true);
    $summary = json_decode((string)($record['summary_payload'] ?? ''), true);
    if (!is_array($details)) $details = [];
    if (!is_array($summary)) $summary = [];
}

function rm($v) {
    return 'RM ' . number_format((float)$v, 2);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>View Cost Calculator Record — ACE Admin</title>
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
      <h4 class="mb-0">Cost Calculator Record</h4>
      <div class="d-flex gap-2">
        <a href="/ACE/admin/cost_calculator_records.php" class="btn btn-outline-secondary btn-sm">Back to List</a>
        <a href="/ACE/admin/directcost.php?edit_id=<?= (int)$recordId ?>" class="btn btn-outline-warning btn-sm">Edit Record</a>
        <?php if ($record && (($record['created_by_username'] ?? '') === $admin_username)): ?>
          <form method="post" action="/ACE/admin/cost_calculator_delete.php" class="d-inline" onsubmit="return confirm('Delete this calculator record?');">
            <?= ace_csrf_input(); ?>
            <input type="hidden" name="record_id" value="<?= (int)$recordId ?>">
            <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
          </form>
        <?php endif; ?>
        <a href="/ACE/admin/directcost.php" class="btn btn-accent btn-sm">Open Calculator</a>
      </div>
    </div>

    <?php if ($errorMessage): ?>
      <div class="alert alert-warning"><?= htmlspecialchars($errorMessage) ?></div>
    <?php else: ?>
      <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="p-3 bg-light rounded"><strong>Name:</strong> <?= htmlspecialchars($record['calc_name']) ?></div></div>
        <div class="col-md-4"><div class="p-3 bg-light rounded"><strong>Created by:</strong> <?= htmlspecialchars($record['created_by_username']) ?></div></div>
        <div class="col-md-4"><div class="p-3 bg-light rounded"><strong>Created at:</strong> <?= htmlspecialchars($record['created_at']) ?></div></div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="p-3 bg-light rounded"><strong>Participants:</strong> <?= htmlspecialchars(number_format((float)$record['participants'], 2)) ?></div></div>
        <div class="col-md-4"><div class="p-3 bg-light rounded"><strong>Suggested fee:</strong> <?= htmlspecialchars(number_format((float)$record['suggested_fee'], 2)) ?></div></div>
        <div class="col-md-4"><div class="p-3 bg-light rounded"><strong>Profit margin:</strong> <?= htmlspecialchars(number_format((float)$record['profit_margin'], 2)) ?>%</div></div>
      </div>

      <h5 class="mt-4">Summary</h5>
      <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle">
          <tbody>
            <tr><th style="width:45%">Expected Total Expenses</th><td><?= rm($record['expected_total_expenses']) ?></td></tr>
            <tr><th>Contingency</th><td><?= rm($record['contingency']) ?></td></tr>
            <tr><th>Subtotal After Contingency</th><td><?= rm($record['subtotal_after_contingency']) ?></td></tr>
            <tr><th>Other Management Service Charges</th><td><?= rm($record['management_service_charges']) ?></td></tr>
            <tr><th>Subtotal After Service Charges</th><td><?= rm($record['subtotal_after_service_charges']) ?></td></tr>
            <tr><th>Profit Margin Amount</th><td><?= rm($record['profit_amount']) ?></td></tr>
            <tr><th>Subtotal After Profit Margin</th><td><?= rm($record['subtotal_after_profit_margin']) ?></td></tr>
            <tr><th>HRD Corp Charges</th><td><?= rm($record['hrd_corp_charges']) ?></td></tr>
            <tr><th>Subtotal After HRD Corp Charges</th><td><?= rm($record['subtotal_after_hrd_charges']) ?></td></tr>
            <tr><th>Minimum Fee Per Participant</th><td><?= rm($record['minimum_fee_per_participant']) ?></td></tr>
            <tr><th>Minimum Participants to Cover Cost</th><td><?= (int)$record['minimum_participants_to_cover_cost'] ?></td></tr>
          </tbody>
        </table>
      </div>

      <h5 class="mt-3">Section Subtotals</h5>
      <div class="row g-3 mb-3">
        <div class="col-md-2"><div class="p-3 bg-light rounded text-center"><strong>[A]</strong><br><?= rm($record['subtotal_a']) ?></div></div>
        <div class="col-md-2"><div class="p-3 bg-light rounded text-center"><strong>[B]</strong><br><?= rm($record['subtotal_b']) ?></div></div>
        <div class="col-md-2"><div class="p-3 bg-light rounded text-center"><strong>[C]</strong><br><?= rm($record['subtotal_c']) ?></div></div>
        <div class="col-md-2"><div class="p-3 bg-light rounded text-center"><strong>[D]</strong><br><?= rm($record['subtotal_d']) ?></div></div>
        <div class="col-md-2"><div class="p-3 bg-light rounded text-center"><strong>[E]</strong><br><?= rm($record['subtotal_e']) ?></div></div>
      </div>

      <details>
        <summary class="mb-2">Show full saved payload (debug)</summary>
        <pre class="bg-light p-3 rounded" style="max-height: 320px; overflow:auto;"><?= htmlspecialchars(json_encode(['summary' => $summary, 'details' => $details], JSON_PRETTY_PRINT)) ?></pre>
      </details>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
