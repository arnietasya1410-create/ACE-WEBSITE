<?php
$khunPath = realpath(__DIR__ . '/../khun.php');
$khunLoaded = false;
if ($khunPath && file_exists($khunPath)) {
    require_once $khunPath;
    $khunLoaded = true;
}

$newsletters = [];

// Load all newsletters from DB
if ($khunLoaded && isset($conn) && $conn instanceof mysqli) {
    $q = $conn->query(
        "SELECT newsletter_id, title, summary, image_url, full_newsletter_url, DATE_FORMAT(created_at, '%Y-%m-%d') AS created_at 
         FROM newsletters 
         ORDER BY created_at DESC"
    );
    
    if ($q && $q->num_rows > 0) {
        while ($row = $q->fetch_assoc()) {
            $newsletters[] = $row;
        }
    }
}

// Fallback to JSON
if (empty($newsletters)) {
    $newsFile = __DIR__ . '/data/newsletters.json';
    if (file_exists($newsFile)) {
        $raw = @file_get_contents($newsFile);
        $arr = json_decode($raw, true);
        if (is_array($arr)) {
            usort($arr, function($a, $b){ return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0); });
            $newsletters = $arr;
        }
    }
}

// Group newsletters by year
$newslettersByYear = [];
foreach ($newsletters as $newsletter) {
    $year = date('Y', strtotime($newsletter['created_at'] ?? 'now'));
    if (!isset($newslettersByYear[$year])) {
        $newslettersByYear[$year] = [];
    }
    $newslettersByYear[$year][] = $newsletter;
}

// Sort years in descending order
krsort($newslettersByYear);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Newsletters — ACE</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="front.css">
  <style>
    :root{--accent:#6f42c1;--muted:#f4f4f7}
    body{font-family:'Poppins',system-ui, -apple-system, "Segoe UI", Roboto, Arial;margin:0;padding-top:78px;background:var(--muted)}
    header.navbar-fixed{position:fixed;top:0;left:0;right:0;z-index:1100;background:rgba(255,255,255,0.92);border-bottom:1px solid rgba(0,0,0,0.05)}
    .btn-primary{background:var(--accent);border-color:var(--accent)}
    nav a.active, nav a:hover { color: var(--accent) !important; }
    .newsletter-img { height:200px; object-fit:cover; border-radius:6px 0 0 6px; background:#e0e0e0; }
    .year-divider {
      font-size: 2rem;
      font-weight: 700;
      color: var(--accent);
      margin: 3rem 0 1.5rem 0;
      padding-bottom: 0.5rem;
      border-bottom: 3px solid var(--accent);
      position: relative;
    }
    .year-divider:first-of-type {
      margin-top: 1rem;
    }
    .year-divider::after {
      content: '';
      position: absolute;
      bottom: -3px;
      left: 0;
      width: 100px;
      height: 3px;
      background: var(--accent);
      opacity: 0.3;
    }
    .newsletter-card {
      transition: transform 0.2s, box-shadow 0.2s;
      border-left: 4px solid transparent;
    }
    .newsletter-card:hover {
      transform: translateX(5px);
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
      border-left-color: var(--accent);
    }
  </style>
</head>
<body>
<?php require_once __DIR__ . '/partials/header.php'; ?>

<main class="container section-card py-5">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h3 class="mb-0">All Newsletters</h3>
      <small class="text-muted">Stay updated with latest news</small>
    </div>
    <div>
      <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to home
      </a>
    </div>
  </div>

  <?php if (empty($newsletters)): ?>
    <div class="alert alert-info">
      <i class="bi bi-info-circle me-2"></i>No newsletters available yet.
    </div>
  <?php else: ?>
    <?php foreach($newslettersByYear as $year => $yearNewsletters): ?>
      <div class="year-divider">
        <i class="bi bi-calendar-event me-2"></i><?= $year ?>
        <small class="text-muted ms-3" style="font-size: 1rem; font-weight: 400;">
          (<?= count($yearNewsletters) ?> newsletter<?= count($yearNewsletters) > 1 ? 's' : '' ?>)
        </small>
      </div>
      
      <div class="row g-4">
        <?php foreach($yearNewsletters as $n):
          $title = htmlspecialchars($n['title'] ?? 'Untitled');
          $summary = htmlspecialchars(mb_strimwidth($n['summary'] ?? '', 0, 250, '...'));
          $date = isset($n['created_at']) ? date('j F Y', strtotime($n['created_at'])) : date('j F Y');
          $img = !empty($n['image_url']) ? htmlspecialchars($n['image_url']) : null;
          $fullUrl = !empty($n['full_newsletter_url']) ? htmlspecialchars($n['full_newsletter_url']) : '#';
        ?>
        <div class="col-12">
          <div class="bg-white rounded shadow-sm newsletter-card">
            <div class="row g-0">
              <div class="col-md-3">
                <div class="newsletter-img w-100 h-100" 
                     style="background-image: url('<?= $img ? $img : '/ACE/images/newsletter-default.jpg' ?>'); 
                            background-size: cover; 
                            background-position: center;
                            min-height: 200px;">
                </div>
              </div>
              <div class="col-md-9">
                <div class="p-4">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h4 class="mb-0" style="color:var(--accent)"><?= $title ?></h4>
                    <span class="badge bg-secondary ms-2"><?= $date ?></span>
                  </div>
                  <p class="text-muted mb-3" style="line-height: 1.6;"><?= $summary ?></p>
                  <div>
                    <a href="<?= $fullUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                      <i class="bi bi-file-text me-2"></i>Read Full Newsletter
                      <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>