<?php
require_once __DIR__ . '/_inc.php';
require_admin(); // Allows both roles

$admin_username = $_SESSION['admin_user'] ?? 'Admin';
$programme = null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Load programme if editing
if ($id && isset($conn) && $conn instanceof mysqli) {
    $stmt = $conn->prepare("SELECT programme_id, title, programme_category, description, start_date, end_date, location, price, is_active, form_url, has_packages, person_in_charge FROM programmes WHERE programme_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $programme = $row;
        }
        $stmt->close();
    }
}

// Load all payment methods
$payment_methods = [];
$q = $conn->query("SELECT payment_method_id, name, label FROM payment_methods ORDER BY payment_method_id ASC");
if ($q) {
    while ($row = $q->fetch_assoc()) {
        $payment_methods[] = $row;
    }
}

// Load selected payment methods and their custom messages for this programme
$selected_payments = [];
if ($id && isset($conn) && $conn instanceof mysqli) {
    $stmt = $conn->prepare("SELECT payment_method_id, message FROM programme_payment_methods WHERE programme_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $selected_payments[$row['payment_method_id']] = $row['message'];
        }
        $stmt->close();
    }
}

// Load images for this programme
$programme_images = [];
if ($id && isset($conn) && $conn instanceof mysqli) {
    $q = $conn->query("SELECT image_id, filename FROM programme_images WHERE programme_id = $id ORDER BY created_at ASC");
    if ($q) {
        while ($row = $q->fetch_assoc()) {
            $programme_images[] = $row;
        }
    }
}

$flash = function_exists('flash_get') ? flash_get() : null;
$page_title = $programme ? 'Edit programme' : 'Create programme';

$category_options = [
    'short_course' => 'Short Course',
    'certificate' => 'Professional Certificate',
    'odl' => 'Open and Distance Learning (ODL)',
    'micro_credential' => 'Micro-Credential',
    'apel' => 'APEL (Accreditation of Prior Experiential Learning)'
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
  <style>
    .form-section { margin-bottom:24px; }
    .form-section h6 { color:var(--accent); margin-bottom:12px; font-weight:600; }
    .payment-method-box { border:1px solid #e0e0e0; border-radius:6px; padding:12px; margin-bottom:10px; background:#fff; }
    .payment-method-box.selected { border-color:var(--accent); background:rgba(111,66,193,0.05); }
    .file-input-label { display:inline-block; padding:8px 16px; background:var(--accent); color:#fff; border-radius:4px; cursor:pointer; font-size:0.9rem; transition:background .2s; }
    .file-input-label:hover { background:var(--accent-600); text-decoration:none; }
    input[type="file"] { display:none; }
    .additional-image { display:inline-block; position:relative; margin:8px; }
    .additional-image img { width:120px; height:120px; object-fit:cover; border-radius:6px; }
    .additional-image .remove-btn { position:absolute; top:-8px; right:-8px; background:red; color:#fff; border:none; border-radius:50%; width:24px; height:24px; cursor:pointer; font-size:12px; }
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

<section class="section-card">
  <div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h4 class="mb-0"><?= htmlspecialchars($page_title) ?></h4>
        <small class="text-muted">Fill in the programme details below</small>
      </div>
      <div>
        <a href="/ACE/admin/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-info"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <div class="row">
      <div class="col-lg-8">
        <div class="p-4 bg-white rounded shadow-sm">
          <form method="post" action="/ACE/admin/program_save.php" enctype="multipart/form-data" novalidate>
            <?= ace_csrf_input(); ?>
            <input type="hidden" name="programme_id" value="<?= $programme ? (int)$programme['programme_id'] : '' ?>">

            <!-- Basic Info -->
            <div class="form-section">
              <h6>Basic Information</h6>
              
              <div class="mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" placeholder="Programme title" value="<?= $programme ? htmlspecialchars($programme['title']) : '' ?>" required>
                <small class="text-muted">e.g. Advanced Project Management</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Programme Category *</label>
                <select name="programme_category" class="form-select" required>
                  <option value="">Select category...</option>
                  <?php foreach ($category_options as $key => $label): ?>
                  <option value="<?= $key ?>" <?= ($programme && $programme['programme_category'] === $key) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
                <small class="text-muted">Select the type of programme</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="5" placeholder="Programme description, objectives, and details"><?= $programme ? htmlspecialchars($programme['description']) : '' ?></textarea>
                <small class="text-muted">Describe what participants will learn</small>
              </div>
            </div>

            <hr>

            <!-- Programme Images -->
            <div class="form-section">
              <h6>Programme Images</h6>
              
              <div class="image-upload-wrapper">
                <label class="file-input-label" for="programme_images_upload">
                  <i class="bi bi-images"></i> Upload images
                </label>
                <input type="file" id="programme_images_upload" name="programme_images[]" accept="image/*" multiple>
                <small class="d-block text-muted mt-2">Select multiple images (JPG, PNG, GIF - max 5MB each)</small>
              </div>

              <?php if (!empty($programme_images)): ?>
                <div class="mt-3">
                  <small class="text-muted d-block mb-2">Existing images:</small>
                  <div id="existing-images">
                    <?php foreach ($programme_images as $img): ?>
                      <div class="additional-image" data-image-id="<?= $img['image_id'] ?>">
                        <img src="<?= htmlspecialchars($img['filename']) ?>" alt="programme image">
                        <button type="button" class="remove-btn" onclick="removeImage(<?= $img['image_id'] ?>)">×</button>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <div id="image-preview-wrapper" class="mt-3" style="display:none;">
                <small class="text-muted d-block mb-2">New images preview:</small>
                <div id="image-previews"></div>
              </div>
            </div>

            <hr>

            <!-- Schedule & Location -->
            <div class="form-section">
              <h6>Schedule & Location</h6>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Start date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $programme ? htmlspecialchars($programme['start_date']) : '' ?>">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">End date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $programme ? htmlspecialchars($programme['end_date']) : '' ?>">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" placeholder="e.g. UniKL RCMP Campus, Room 101" value="<?= $programme ? htmlspecialchars($programme['location']) : '' ?>">
              </div>
            </div>

            <hr>

            <!-- Pricing -->
            <div class="form-section">
              <h6>Pricing</h6>
              
              <div class="mb-3">
                <label class="form-label">Price (RM) *</label>
                <input type="number" name="price" class="form-control" step="0.01" min="0" placeholder="0.00" value="<?= $programme ? htmlspecialchars($programme['price']) : '' ?>" <?= ($programme && $programme['has_packages']) ? '' : 'required' ?>>
                <small class="text-muted">Enter price in Malaysian Ringgit</small>
              </div>

              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="has_packages" id="has_packages" value="1" <?= ($programme && $programme['has_packages']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="has_packages">
                    This programme has <strong>payment packages</strong> (multiple pricing options). When enabled, price displayed to users will show <em>"Refer to Programme description"</em> instead of a fixed amount.
                  </label>
                </div>
              </div>
            </div>

            <hr>

            <!-- Payment Methods & Custom Messages -->
            <div class="form-section">
              <h6>Payment Methods & Custom Messages</h6>
              <small class="text-muted d-block mb-3">Select payment methods and add custom instructions for each</small>
              
              <?php if (empty($payment_methods)): ?>
                <div class="alert alert-warning">No payment methods available. Please add them first.</div>
              <?php else: ?>
                <?php foreach ($payment_methods as $pm): 
                  $pmId = $pm['payment_method_id'];
                  $isChecked = isset($selected_payments[$pmId]);
                  $customMessage = $isChecked ? $selected_payments[$pmId] : '';
                ?>
                <div class="payment-method-box <?= $isChecked ? 'selected' : '' ?>" data-pm-id="<?= $pmId ?>">
                  <div class="form-check mb-2">
                    <input class="form-check-input payment-checkbox" type="checkbox" name="payment_methods[]" value="<?= $pmId ?>" id="pm_<?= $pmId ?>" <?= $isChecked ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold" for="pm_<?= $pmId ?>">
                      <?= htmlspecialchars($pm['label']) ?>
                    </label>
                  </div>
                  <div class="payment-message-wrapper">
                    <label class="form-label small text-muted">Custom message for users</label>
                    <textarea name="payment_message[<?= $pmId ?>]" class="form-control form-control-sm" rows="2" placeholder="e.g. Transfer to Account 123456789, Bank XYZ"><?= htmlspecialchars($customMessage) ?></textarea>
                    <small class="text-muted">This message will be shown to users when they select this payment method</small>
                  </div>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <hr>
            

            <!-- Status -->
            <div class="form-section">
              <h6>Registration Settings</h6>
              
              <div class="mb-3">
                <label class="form-label">Google Form URL</label>
                <input type="url" name="form_url" class="form-control" placeholder="https://docs.google.com/forms/..." value="<?= $programme ? htmlspecialchars($programme['form_url'] ?? '') : '' ?>">
                <small class="text-muted d-block mt-1">"Register" on the public page will link to this Google Form.</small>
              </div>

              <div class="mb-3">
                <div class="form-check">
                  <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" <?= ($programme && $programme['is_active']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="is_active">
                    Active (visible to users)
                  </label>
                </div>
              </div>

            </div>

            <hr>

            <!-- Person In Charge -->
            <div class="form-section">
              <h6>Contact Information</h6>
              
              <div class="mb-3">
                <label class="form-label">Person In Charge Contact</label>
                <textarea name="person_in_charge" class="form-control" rows="3" placeholder="e.g. John Doe&#10;012-345-6789&#10;john@example.com"><?= $programme ? htmlspecialchars($programme['person_in_charge'] ?? '') : '' ?></textarea>
                <small class="text-muted">Name and contact details of the person managing this programme (supports multiple lines)</small>
              </div>
            </div>

            <hr>

            <!-- Actions -->
             <div class="d-flex gap-2">
               <button type="submit" class="btn btn-accent">
                 <?= $programme ? 'Update programme' : 'Create programme' ?>
               </button>
               <a href="/ACE/admin/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
             </div>
           </form>
         </div>
      </div>

      <!-- Side info -->
      <div class="col-lg-4">
        <div class="p-3 bg-light rounded small">
          <h6 style="color:var(--accent)">Tips</h6>
          <ul class="mb-0">
            <li>Use clear, descriptive titles</li>
            <li>Select appropriate category</li>
            <li>Include learning outcomes in description</li>
            <li>Upload multiple images to showcase programme</li>
            <li>Set realistic start/end dates</li>
            <li>Specify exact location or online</li>
            <li>Add payment instructions for each method</li>
            <li>Mark as active when ready to publish</li>
          </ul>
        </div>
      </div>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle payment method box styling
document.querySelectorAll('.payment-checkbox').forEach(function(checkbox) {
  checkbox.addEventListener('change', function() {
    const box = this.closest('.payment-method-box');
    if (this.checked) {
      box.classList.add('selected');
    } else {
      box.classList.remove('selected');
    }
  });
});

// Programme images preview
document.getElementById('programme_images_upload').addEventListener('change', function(e){
  const files = e.target.files;
  const wrapper = document.getElementById('image-preview-wrapper');
  const container = document.getElementById('image-previews');
  container.innerHTML = '';
  
  if (files.length > 0) {
    wrapper.style.display = 'block';
    Array.from(files).forEach(file => {
      const reader = new FileReader();
      reader.onload = function(event){
        const div = document.createElement('div');
        div.className = 'additional-image';
        div.innerHTML = '<img src="' + event.target.result + '" alt="preview">';
        container.appendChild(div);
      };
      reader.readAsDataURL(file);
    });
  } else {
    wrapper.style.display = 'none';
  }
});

// Remove existing image
function removeImage(imageId) {
  if (!confirm('Remove this image?')) return;
  
  fetch('/ACE/admin/program_image_delete.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({image_id: imageId})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.querySelector('[data-image-id="' + imageId + '"]').remove();
    } else {
      alert('Failed to remove image');
    }
  });
}
</script>
</body>
</html>