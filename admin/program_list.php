<?php
require_once __DIR__ . '/_inc.php';
require_admin(); // This allows both admin and super_admin

$admin_username = $_SESSION['admin_user'] ?? 'Admin';

// Search and filter handlers
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT programme_id, title, programme_category, description, start_date, end_date, location, price, has_packages, is_active, person_in_charge
        FROM programmes WHERE 1=1 ";

$params = [];
$types = '';

if ($search !== '') {
    $sql .= "AND (title LIKE ? OR description LIKE ?) ";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

if ($filter_category !== '') {
    $sql .= "AND programme_category = ? ";
    $params[] = $filter_category;
    $types .= 's';
}

$sql .= "ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$programmes = $result->fetch_all(MYSQLI_ASSOC);

$page_title = "Programmes List";

$category_names = [
    'short_course' => 'Short Course',
    'certificate' => 'Professional Certificate',
    'odl' => 'Open and Distance Learning (ODL)',
    'micro_credential' => 'Micro-Credential',
    'apel' => 'APEL'
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?> — ACE Admin</title>
  <link rel="stylesheet" href="/ACE/User/front.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .programme-row {
      transition: all 0.2s;
      cursor: pointer;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      margin-bottom: 16px;
    }
    .programme-row:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    
    .badge-short_course { background: rgba(13,202,240,0.12); color: #0dcaf0; }
    .badge-certificate { background: rgba(111,66,193,0.12); color: #6f42c1; }
    .badge-odl { background: rgba(40,167,69,0.12); color: #28a745; }
    .badge-micro_credential { background: rgba(253,126,20,0.12); color: #fd7e14; }
    .badge-apel { background: rgba(13,110,253,0.12); color: #0d6efd; }

    .modal-dialog { max-width: 900px; }
    .modal-header {
      display: flex !important;
      align-items: flex-start !important;
      gap: 12px !important;
      background: var(--accent) !important;
      color: #fff !important;
      padding: 16px 20px !important;
      flex-wrap: nowrap;
    }

    .modal-header .modal-title,
    .modal-header h5,
    .modal-header .title {
      flex: 1 1 auto !important;
      max-width: 100% !important;
      white-space: normal !important;
      word-break: break-word !important;
      overflow: visible !important;
      margin: 0 !important;
      line-height: 1.25 !important;
      font-weight: 600;
    }

    .modal-header .btn-close {
      flex: 0 0 auto !important;
      margin-left: 12px !important;
      position: relative !important;
      top: 0 !important;
    }

    .filter-card {
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<?php 
// Use appropriate header based on role
if (is_super_admin()) {
    require_once __DIR__ . '/partials/header_super.php'; 
} else {
    require_once __DIR__ . '/partials/header.php'; 
}
?>

<section class="section-card">
  <div class="container py-4">

    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h4 class="mb-0"><?= htmlspecialchars($page_title) ?></h4>
        <small class="text-muted">Browse and manage all programmes</small>
      </div>
      <a href="/ACE/admin/program_edit.php" class="btn btn-accent btn-sm">+ Create New</a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="filter-card">
      <form method="get" action="">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Search Programme</label>
            <input type="text" name="search" class="form-control" placeholder="Search by title or description..." value="<?= htmlspecialchars($search) ?>">
          </div>
          
          <div class="col-md-4">
            <label class="form-label">Filter by Category</label>
            <select name="category" class="form-select">
              <option value="">All Categories</option>
              <option value="short_course" <?= $filter_category === 'short_course' ? 'selected' : '' ?>>Short Course</option>
              <option value="certificate" <?= $filter_category === 'certificate' ? 'selected' : '' ?>>Professional Certificate</option>
              <option value="odl" <?= $filter_category === 'odl' ? 'selected' : '' ?>>Open and Distance Learning (ODL)</option>
              <option value="micro_credential" <?= $filter_category === 'micro_credential' ? 'selected' : '' ?>>Micro-Credential</option>
              <option value="apel" <?= $filter_category === 'apel' ? 'selected' : '' ?>>APEL</option>
            </select>
          </div>
          
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-accent w-100 me-2" type="submit">
              <i class="bi bi-funnel"></i> Filter
            </button>
            <a href="program_list.php" class="btn btn-outline-secondary">
              <i class="bi bi-x-circle"></i>
            </a>
          </div>
        </div>
      </form>
      
      <!-- Results Count -->
      <div class="mt-3">
        <small class="text-muted">
          Found <strong><?= count($programmes) ?></strong> programme(s)
          <?php if ($search !== ''): ?>
            matching "<strong><?= htmlspecialchars($search) ?></strong>"
          <?php endif; ?>
          <?php if ($filter_category !== ''): ?>
            in category "<strong><?= htmlspecialchars($category_names[$filter_category] ?? $filter_category) ?></strong>"
          <?php endif; ?>
        </small>
      </div>
    </div>

    <!-- Programme List (Top to Bottom) -->
    <?php if (empty($programmes)): ?>
      <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No programmes found. 
        <?php if ($search !== '' || $filter_category !== ''): ?>
          <a href="program_list.php" class="alert-link">Clear filters</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <?php foreach ($programmes as $p): 
        $category_label = $category_names[$p['programme_category']] ?? 'Programme';
        $badge_class = 'badge-' . ($p['programme_category'] ?? 'short_course');
      ?>
      <div class="programme-row p-3 d-flex align-items-center justify-content-between"
           data-bs-toggle="modal"
           data-bs-target="#programmeModal<?= $p['programme_id'] ?>">
        
        <div class="flex-grow-1">
          <div class="mb-2">
            <span class="badge <?= $badge_class ?> me-2"><?= htmlspecialchars($category_label) ?></span>
            <span class="badge <?= $p['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
              <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
          </div>
          <h6 class="mb-1"><?= htmlspecialchars($p['title']) ?></h6>
          <small class="text-muted">
            <?= $p['start_date'] ? date("d M Y", strtotime($p['start_date'])) : 'No date' ?>
            → 
            <?= $p['end_date'] ? date("d M Y", strtotime($p['end_date'])) : 'No date' ?>
            <?php if (!empty($p['location'])): ?>
              | <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($p['location']) ?>
            <?php endif; ?>
          </small>
          <p class="mt-2 mb-0 small text-muted">
            <?= htmlspecialchars(substr($p['description'], 0, 150)) ?><?= strlen($p['description']) > 150 ? '...' : '' ?>
          </p>
        </div>

        <div class="text-end ms-3">
          <?php if (!empty($p['has_packages'])): ?>
            <span class="text-muted small">Refer to Programme description</span>
          <?php elseif (!empty($p['price'])): ?>
            <strong class="d-block text-primary">RM <?= number_format($p['price'], 2) ?></strong>
          <?php endif; ?>
        </div>
      </div>

      <!-- Modal for programme details -->
      <div class="modal fade" id="programmeModal<?= $p['programme_id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title"><?= htmlspecialchars($p['title']) ?></h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              
              <div class="mb-3">
                <strong>Category:</strong><br>
                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($category_label) ?></span>
              </div>

              <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>

              <hr>

              <div class="row mb-3">
                <div class="col-md-6">
                  <strong>Start Date:</strong><br>
                  <?= $p['start_date'] ? date("d M Y", strtotime($p['start_date'])) : '-' ?>
                </div>
                <div class="col-md-6">
                  <strong>End Date:</strong><br>
                  <?= $p['end_date'] ? date("d M Y", strtotime($p['end_date'])) : '-' ?>
                </div>
              </div>

              <div class="mb-3">
                <strong>Location:</strong><br>
                <?= htmlspecialchars($p['location']) ?: '-' ?>
              </div>

              <div class="mb-3">
                <strong>Price (RM):</strong><br>
                <?php if (!empty($p['has_packages'])): ?>
                  <em>Refer to Programme description</em>
                <?php else: ?>
                  <?= number_format($p['price'], 2) ?>
                <?php endif; ?>
              </div>

              <div class="mb-3">
                <strong>Status:</strong><br>
                <span class="badge <?= $p['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                  <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </div>

              <?php if (!empty($p['person_in_charge'])): ?>
              <div class="mb-3">
                <strong>Person In Charge:</strong><br>
                <?= htmlspecialchars($p['person_in_charge']) ?>
              </div>
              <?php endif; ?>

            </div>

            <div class="modal-footer">
              <a href="/ACE/admin/program_edit.php?id=<?= $p['programme_id'] ?>" class="btn btn-accent">
                <i class="bi bi-pencil"></i> Edit
              </a>

              <form method="post" action="/ACE/admin/program_delete.php" class="d-inline" onsubmit="return confirm('Delete this programme? This action cannot be undone.');">
                <?= ace_csrf_input(); ?>
                <input type="hidden" name="programme_id" value="<?= $p['programme_id'] ?>">
                <button type="submit" class="btn btn-outline-danger">
                  <i class="bi bi-trash"></i> Delete
                </button>
              </form>

              <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
