<?php
require_once __DIR__ . '/_inc.php';
require_admin();

$admin_username = $_SESSION['admin_user'] ?? 'Admin';


// load programmes from DB if available, else fallback to JSON
$programmes = [];

if (isset($conn) && $conn instanceof mysqli) {
    $res = $conn->query("SHOW TABLES LIKE 'programmes'");
    if ($res && $res->num_rows) {
        $q = $conn->query("SELECT programme_id, title, description, price, DATE_FORMAT(created_at, '%Y-%m-%d') AS created_at FROM programmes WHERE is_active = 1 ORDER BY created_at DESC");
        if ($q) {
            while ($row = $q->fetch_assoc()) $programmes[] = $row;
        }
    }
}

// fallback to JSON file
if (empty($programmes)) {
    $jsonFile = __DIR__ . '/data/programmes.json';
    if (file_exists($jsonFile)) {
        $raw = @file_get_contents($jsonFile);
        $arr = json_decode($raw, true);
        if (is_array($arr)) $programmes = $arr;
    }
}

$flash = function_exists('flash_get') ? flash_get() : null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin — Dashboard</title>
  <!-- use same front.css used by user pages -->
  <link rel="stylesheet" href="/ACE/User/front.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* small admin-specific tweaks kept lightweight */
    .admin-tiles .card { min-height:140px; }
    .admin-hero { margin-top:6px; margin-bottom:18px; }
  </style>
</head>
<body>

<?php 
if (is_super_admin()) {
    require_once __DIR__ . '/partials/header_super.php'; 
} else {
    require_once __DIR__ . '/partials/header.php'; 
}
?>

<section class="section-card admin-hero">
  <div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div class="text-end">
    <div class="fw-semibold">Welcome, <?= htmlspecialchars($admin_username) ?> 👋</div>
    <small class="text-muted">Have a productive day!</small>
</div>
      <div>
        <h4 class="mb-0">Admin Dashboard</h4>
        <small class="text-muted">Manage programmes, newsletters and tools</small>
      </div>
      <div>
        <a href="/ACE/admin/news_edit.php" class="btn btn-outline-secondary btn-sm">Manage Newsletters</a>
        <a href="/ACE/admin/social_media.php" class="btn btn-outline-secondary btn-sm">Manage Social Media</a>
        <a href="/ACE/admin/change_password.php" class="btn btn-outline-secondary btn-sm">Change Password</a>
        <a href="/ACE/admin/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-info"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <div class="row g-3 admin-tiles mb-4">
      <div class="col-md-4">
        <div class="p-3 bg-white rounded shadow-sm h-100 d-flex flex-column">
          <div class="mb-2">
            <h5 class="mb-1">Create programme</h5>
            <small class="text-muted">Add a new programme (title, description, price).</small>
          </div>
          <div class="mt-auto">
            <a href="/ACE/admin/program_edit.php" class="btn btn-accent btn-sm">Create programme</a>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-3 bg-white rounded shadow-sm h-100 d-flex flex-column">
          <div class="mb-2">
            <h5 class="mb-1">Manage programmes</h5>
            <small class="text-muted">Edit or remove existing programmes.</small>
          </div>
          <div class="mt-auto">
            <a href="/ACE/admin/program_list.php" class="btn btn-accent btn-sm">View programmes</a>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-3 bg-white rounded shadow-sm h-100 d-flex flex-column">
          <div class="mb-2">
            <h5 class="mb-1">Cost calculator</h5>
            <small class="text-muted">Open the direct cost calculator to estimate programme costs.</small>
          </div>
          <div class="mt-auto">
            <a href="/ACE/admin/directcost.php" target="_blank" class="btn btn-accent btn-sm">Open calculator</a>
          </div>
        </div>
      </div>
    </div>

    

    <hr class="my-4">

    <h5 id="program-list" class="mb-3">Programmes</h5>

    <?php if (empty($programmes)): ?>
      <div class="alert alert-warning">No programmes found. Use "Create programme" to add one.</div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($programmes as $p):
            $id = isset($p['programme_id']) ? (int)$p['programme_id'] : null;
            $title = htmlspecialchars($p['title'] ?? 'Untitled');
            $price = isset($p['price']) ? htmlspecialchars($p['price']) : '';
            $created = htmlspecialchars($p['created_at'] ?? '');
            $desc = htmlspecialchars(mb_strimwidth($p['description'] ?? '', 0, 100, '...'));
        ?>
        <div class="col-md-6">
          <div class="p-3 bg-white rounded shadow-sm h-100 d-flex flex-column">
            <div class="mb-2">
              <h6 class="mb-1" style="color:var(--accent)"><?= $title ?></h6>
              <small class="text-muted"><?= $desc ?></small>
              <div class="text-muted small mt-2">Price: RM<?= $price ?: 'N/A' ?> • Created: <?= $created ?></div>
            </div>
            <div class="mt-auto d-flex gap-2">
              <a href="/ACE/admin/program_edit.php?id=<?= $id ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
              <form method="post" action="/ACE/admin/program_delete.php" class="d-inline" onsubmit="return confirm('Delete this programme?');">
                <?= ace_csrf_input(); ?>
                <input type="hidden" name="programme_id" value="<?= $id ?>">
                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>