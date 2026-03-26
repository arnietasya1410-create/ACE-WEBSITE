<?php
require_once __DIR__ . '/../admin/_inc.php';

// Get programme ID from query param
$programme_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch programme details
$programme = null;
if ($programme_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM programmes WHERE programme_id = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param('i', $programme_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $programme = $res->fetch_assoc();
    }
    $stmt->close();
}

if (!$programme) {
    header("Location: /ACE/User/courses.php");
    exit;
}

// Fetch allowed payment methods and their messages for this programme
$payment_methods = [];
$stmt = $conn->prepare("
    SELECT pm.payment_method_id, pm.label, ppm.message
    FROM payment_methods pm
    INNER JOIN programme_payment_methods ppm ON pm.payment_method_id = ppm.payment_method_id
    WHERE ppm.programme_id = ?
");
$stmt->bind_param('i', $programme_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $payment_methods[] = $row;
}
$stmt->close();

// Fetch images for this programme
$images = [];
$stmt = $conn->prepare("SELECT filename FROM programme_images WHERE programme_id = ?");
$stmt->bind_param('i', $programme_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $images[] = $row;
}
$stmt->close();

// Get flash message
$flash_message = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
$flash_type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success';
unset($_SESSION['flash'], $_SESSION['flash_type']);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($programme['title']) ?> — ACE</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="/ACE/User/front.css" />
  <style>
    :root {
      --accent: #6f42c1;
      --accent-light: rgba(111, 66, 193, 0.1);
    }
    .carousel-item img { 
        width: 100%; 
        height: auto; 
        object-fit: contain; 
        max-height: 500px; 
    }
    .payment-message-box {
      background: var(--accent-light);
      border-left: 4px solid var(--accent);
      padding: 16px;
      border-radius: 6px;
      margin-top: 12px;
      animation: slideDown 0.3s ease;
    }
    .payment-message-box strong {
      color: var(--accent);
      display: block;
      margin-bottom: 8px;
    }
    .payment-message-box p {
      margin: 0;
      white-space: pre-wrap;
      word-wrap: break-word;
      color: #333;
    }
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .flash-message {
      animation: slideDown 0.4s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .flash-message .btn-close {
      font-size: 0.8rem;
    }
  </style>
</head>
<body>

<?php require_once __DIR__ . '/partials/header.php'; ?>

<section class="section-card">
  <div class="container py-4">

    <!-- Flash Message -->
    <?php if ($flash_message): ?>
      <div class="alert alert-<?= $flash_type === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show flash-message" role="alert">
        <div class="d-flex align-items-center">
          <i class="bi bi-<?= $flash_type === 'error' ? 'exclamation-triangle' : 'check-circle' ?>-fill me-3" style="font-size: 1.5rem;"></i>
          <div>
            <strong><?= $flash_type === 'error' ? 'Oops!' : 'Success!' ?></strong>
            <p class="mb-0"><?= htmlspecialchars($flash_message) ?></p>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <h2 class="fw-bold mb-3"><?= htmlspecialchars($programme['title']) ?></h2>
    
    <!-- IMAGE CAROUSEL -->
    <div id="carouselImages" class="carousel slide mb-4" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php if (!empty($images)): ?>
          <?php foreach ($images as $idx => $img): ?>
            <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
              <img src="<?= htmlspecialchars($img['filename']) ?>" class="d-block w-100" alt="Programme Image" onerror="this.src='/ACE/images/no-image.png'" />
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="carousel-item active">
            <img src="/ACE/images/no-image.png" class="d-block w-100" alt="No Image" />
          </div>
        <?php endif; ?>
      </div>

      <?php if (count($images) > 1): ?>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselImages" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
        <span class="visually-hidden">Previous</span>
      </button>

      <button class="carousel-control-next" type="button" data-bs-target="#carouselImages" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
        <span class="visually-hidden">Next</span>
      </button>
      <?php endif; ?>
    </div>

    <p><?= nl2br(htmlspecialchars($programme['description'])) ?></p>

    <ul class="list-group mb-4">
      <li class="list-group-item"><strong>Start Date:</strong> <?= htmlspecialchars($programme['start_date']) ?: '-' ?></li>
      <li class="list-group-item"><strong>End Date:</strong> <?= htmlspecialchars($programme['end_date']) ?: '-' ?></li>
      <li class="list-group-item"><strong>Location:</strong> <?= htmlspecialchars($programme['location']) ?: '-' ?></li>
      <li class="list-group-item"><strong>Price:</strong> <?php if (!empty($programme['has_packages'])): ?>Refer to Programme description<?php else: ?>RM<?= number_format((float)$programme['price'], 2) ?><?php endif; ?></li>
    </ul>

    <!-- Action buttons -->
    <!-- Payment method selector (moved out of removed registration form) -->
    <?php if (!empty($payment_methods)): ?>
      <div class="mb-4">
        <label for="payment_method" class="form-label"><strong>Payment Method</strong></label>
        <select id="payment_method" name="payment_method" class="form-select" required>
          <option value="">Select a payment method</option>
          <?php foreach ($payment_methods as $pm): ?>
            <option value="<?= $pm['payment_method_id'] ?>" data-message="<?= htmlspecialchars($pm['message']) ?>">
              <?= htmlspecialchars($pm['label']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <!-- Payment message display area -->
        <div id="paymentMessageWrapper" style="display:none; margin-top:12px;">
          <div class="payment-message-box">
            <strong>Payment Instructions:</strong>
            <p id="paymentMessage"></p>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="mb-4">
      <?php if (!empty($programme['form_url'])): ?>
        <a href="<?= htmlspecialchars($programme['form_url']) ?>" target="_blank" rel="noopener" class="btn btn-success me-2">
          Register Now
        </a>
      <?php else: ?>
        <button id="btnRegister" class="btn btn-success me-2">Register Now</button>
      <?php endif; ?>

      <button id="btnQueries" class="btn btn-primary">Queries</button>
    </div>

    <!-- Query form (hidden by default) -->
    <div id="queryForm" class="card p-4" style="display:none;">
      <h4>Submit a Query</h4>
      <form action="/ACE/User/submit_query.php" method="POST" novalidate>
        <?= ace_csrf_input(); ?>
        <input type="hidden" name="programme_id" value="<?= $programme_id ?>" />

        <div class="mb-3">
          <label for="query_full_name" class="form-label">Full Name *</label>
          <input type="text" id="query_full_name" name="full_name" class="form-control" required />
        </div>

        <div class="mb-3">
          <label for="query_email" class="form-label">Email *</label>
          <input type="email" id="query_email" name="email" class="form-control" required />
        </div>

        <div class="mb-3">
          <label for="query_phone" class="form-label">Phone Number</label>
          <input type="text" id="query_phone" name="phone" class="form-control" />
        </div>

        <div class="mb-3">
          <label for="message" class="form-label">Message *</label>
          <textarea id="message" name="message" rows="4" class="form-control" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Query</button>
      </form>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Toggle query form visibility (register form removed)
  const btnRegister = document.getElementById('btnRegister');
  const btnQueries = document.getElementById('btnQueries');
  const queryForm = document.getElementById('queryForm');

  if (btnRegister) {
    btnRegister.addEventListener('click', () => {
      const paymentSelect = document.getElementById('payment_method');
      const pm = paymentSelect ? paymentSelect.value : '';
      if (paymentSelect && !pm) {
        alert('Please select a payment method before proceeding to register.');
        paymentSelect.focus();
        return;
      }

      // Redirect to internal registration handler (adjust path if needed)
      window.location.href = '/ACE/User/submit_registration.php?programme_id=<?= $programme_id ?>&payment_method=' + encodeURIComponent(pm);
    });
  }

  if (btnQueries) {
    btnQueries.addEventListener('click', () => {
      if (!queryForm) return;
      queryForm.style.display = queryForm.style.display === 'none' ? 'block' : 'none';
    });
  }

  // Payment method message display (works with payment selector moved)
  const pmEl = document.getElementById('payment_method');
  if (pmEl) {
    pmEl.addEventListener('change', function() {
      var selected = this.options[this.selectedIndex];
      var msg = selected.getAttribute('data-message');
      var wrapper = document.getElementById('paymentMessageWrapper');
      var msgElement = document.getElementById('paymentMessage');

      if (msg && msg.trim() !== '') {
        msgElement.textContent = msg;
        wrapper.style.display = 'block';
      } else {
        wrapper.style.display = 'none';
      }
    });
  }
</script>

</body>
</html>
