<?php
require_once __DIR__ . '/_inc.php';
require_admin(); // Allows both admin and super_admin

$admin_username = $_SESSION['admin_user'] ?? 'Admin';

$newsletter = null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Load newsletter if editing
if ($id && isset($conn) && $conn instanceof mysqli) {
    $stmt = $conn->prepare("SELECT newsletter_id, title, summary, image_url, full_newsletter_url, DATE_FORMAT(created_at, '%Y-%m-%d') AS created_at FROM newsletters WHERE newsletter_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $newsletter = $row;
        }
        $stmt->close();
    }
}

$flash = function_exists('flash_get') ? flash_get() : null;
$page_title = $newsletter ? 'Edit newsletter' : 'Create newsletter';

// Load all newsletters for list view
$newsletters = [];
if (isset($conn) && $conn instanceof mysqli) {
    $q = $conn->query("SELECT newsletter_id, title, DATE_FORMAT(created_at, '%Y-%m-%d') AS created_at FROM newsletters ORDER BY created_at DESC");
    if ($q) {
        while ($row = $q->fetch_assoc()) {
            $newsletters[] = $row;
        }
    }
}
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
    .form-section { margin-bottom:24px; }
    .form-section h6 { color:var(--accent); margin-bottom:12px; font-weight:600; }
    .news-list-item { padding:12px; border:1px solid #e0e0e0; border-radius:6px; margin-bottom:8px; cursor:pointer; transition:all .2s; }
    .news-list-item:hover { background:#f9f9f9; border-color:var(--accent); }
    .news-list-item.active { background:rgba(111,66,193,0.08); border-color:var(--accent); }
    .image-upload-wrapper { position:relative; }
    .image-preview { max-width:100%; height:auto; border-radius:6px; max-height:200px; margin-top:12px; }
    .file-input-label { display:inline-block; padding:8px 16px; background:var(--accent); color:#fff; border-radius:4px; cursor:pointer; font-size:0.9rem; transition:background .2s; }
    .file-input-label:hover { background:var(--accent-600); text-decoration:none; }
    input[type="file"] { display:none; }
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
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h4 class="mb-0"><?= htmlspecialchars($page_title) ?></h4>
        <small class="text-muted">Manage newsletters and communications</small>
      </div>
      <div>
        <a href="/ACE/admin/dashboard.php" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-left me-1"></i>Back to dashboard
        </a>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-info alert-dismissible fade show">
        <?= htmlspecialchars($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="row">
      <!-- Form (left) -->
      <div class="col-lg-8">
        <div class="p-4 bg-white rounded shadow-sm">
          <form method="post" action="/ACE/admin/news_save.php" enctype="multipart/form-data" novalidate>
            <?= ace_csrf_input(); ?>
            <input type="hidden" name="newsletter_id" value="<?= $newsletter ? (int)$newsletter['newsletter_id'] : '' ?>">
            <input type="hidden" name="existing_image" value="<?= $newsletter ? htmlspecialchars($newsletter['image_url']) : '' ?>">

            <!-- Basic Info -->
            <div class="form-section">
              <h6><i class="bi bi-info-circle me-2"></i>Newsletter Details</h6>
              
              <div class="mb-3">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="Newsletter title" value="<?= $newsletter ? htmlspecialchars($newsletter['title']) : '' ?>" required>
                <small class="text-muted">e.g. December 2024 Updates</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Summary <span class="text-danger">*</span></label>
                <textarea name="summary" class="form-control" rows="4" placeholder="Brief summary of the newsletter" required><?= $newsletter ? htmlspecialchars($newsletter['summary']) : '' ?></textarea>
                <small class="text-muted">This appears in previews (max 200 characters recommended)</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Publication Date <span class="text-danger">*</span></label>
                <input type="date" name="created_at" class="form-control" 
                       value="<?= $newsletter ? htmlspecialchars($newsletter['created_at']) : date('Y-m-d') ?>" 
                       required 
                       max="<?= date('Y-m-d') ?>">
                <small class="text-muted">
                  <i class="bi bi-info-circle"></i> 
                  This date is used to group newsletters by year on the user page
                </small>
              </div>
            </div>

            <hr>

            <!-- Media -->
            <div class="form-section">
              <h6><i class="bi bi-image me-2"></i>Cover Image</h6>
              
              <div class="image-upload-wrapper">
                <label class="file-input-label" for="image_upload">
                  <i class="bi bi-cloud-upload"></i> Choose image
                </label>
                <input type="file" id="image_upload" name="image_file" accept="image/*">
                <small class="d-block text-muted mt-2">JPG, PNG or GIF (recommended: 600x400px, max 5MB)</small>
              </div>

              <?php if ($newsletter && !empty($newsletter['image_url'])): ?>
                <div class="mt-3">
                  <small class="text-muted d-block mb-2">Current image:</small>
                  <img src="<?= htmlspecialchars($newsletter['image_url']) ?>" alt="preview" class="image-preview">
                  <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input" id="remove_image" name="remove_image" value="1">
                    <label class="form-check-label" for="remove_image">
                      Remove current image
                    </label>
                  </div>
                </div>
              <?php endif; ?>

              <div id="new-preview-wrapper" style="display:none;" class="mt-3">
                <small class="text-muted d-block mb-2">New image preview:</small>
                <img id="new-preview" class="image-preview" alt="preview">
              </div>
            </div>

            <hr>

            <!-- Content -->
            <div class="form-section">
              <h6><i class="bi bi-link-45deg me-2"></i>Full Content</h6>
              
              <div class="mb-3">
                <label class="form-label">Full Newsletter URL</label>
                <input type="url" name="full_newsletter_url" class="form-control" placeholder="https://example.com/newsletter/full-content" value="<?= $newsletter ? htmlspecialchars($newsletter['full_newsletter_url']) : '' ?>">
                <small class="text-muted">Link to full newsletter content (external or internal page)</small>
              </div>
            </div>

            <hr>

            <!-- Actions -->
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-accent">
                <?= $newsletter ? 'Update newsletter' : 'Create newsletter' ?>
              </button>
              <?php if ($newsletter): ?>
                <button type="submit"
                        class="btn btn-danger"
                        formaction="/ACE/admin/news_delete.php"
                        formmethod="post"
                        name="delete_newsletter"
                        value="1"
                        onclick="return confirm('Are you sure you want to delete this newsletter? This action cannot be undone.');">
                  <i class="bi bi-trash"></i> Delete
                </button>
              <?php endif; ?>
              <a href="/ACE/admin/news_edit.php" class="btn btn-outline-secondary">Clear</a>
              <a href="/ACE/admin/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>

      <!-- List (right) -->
      <div class="col-lg-4">
        <div class="p-3 bg-white rounded shadow-sm">
          <h6 style="color:var(--accent)" class="mb-3">Recent newsletters</h6>
          
          <?php if (empty($newsletters)): ?>
            <small class="text-muted">No newsletters yet.</small>
          <?php else: ?>
            <div style="max-height:500px; overflow-y:auto;">
              <?php foreach ($newsletters as $item): 
                $isActive = $newsletter && $newsletter['newsletter_id'] == $item['newsletter_id'];
              ?>
              <a href="?id=<?= $item['newsletter_id'] ?>" class="text-decoration-none">
                <div class="news-list-item <?= $isActive ? 'active' : '' ?>">
                  <div class="small fw-600" style="color:var(--accent)"><?= htmlspecialchars($item['title']) ?></div>
                  <div class="text-muted" style="font-size:0.85rem"><?= htmlspecialchars($item['created_at']) ?></div>
                </div>
              </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Live preview for image upload
  document.getElementById('image_upload').addEventListener('change', function(e){
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(event){
        const wrapper = document.getElementById('new-preview-wrapper');
        const preview = document.getElementById('new-preview');
        preview.src = event.target.result;
        wrapper.style.display = 'block';
      };
      reader.readAsDataURL(file);
    }
  });

</script>
</body>
</html>