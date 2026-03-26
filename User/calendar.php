<?php
$khunPath = realpath(__DIR__ . '/../khun.php');
$khunLoaded = false;
if ($khunPath && file_exists($khunPath)) {
    require_once $khunPath;
    $khunLoaded = true;
}

$events = [];
$today = date('Y-m-d');

// Load upcoming programmes from DB (include ongoing events where end_date >= today)
if ($khunLoaded && isset($conn) && $conn instanceof mysqli) {
    $q = $conn->query(
        "SELECT p.programme_id, p.title, p.description, p.start_date, p.end_date, p.location, p.programme_category,
                (SELECT filename FROM programme_images WHERE programme_id = p.programme_id LIMIT 1) as image_url
         FROM programmes p
         WHERE p.is_active = 1 AND (p.end_date >= '" . $conn->real_escape_string($today) . "' OR p.start_date >= '" . $conn->real_escape_string($today) . "')
         ORDER BY p.start_date ASC"
    );

    if ($q && $q->num_rows > 0) {
        while ($row = $q->fetch_assoc()) {
            $events[] = $row;
        }
    }
}

// Fallback to JSON file
if (empty($events)) {
    $eventsFile = __DIR__ . '/data/programmes.json';
    if (file_exists($eventsFile)) {
        $raw = @file_get_contents($eventsFile);
        $arr = json_decode($raw, true);
        if (is_array($arr)) {
            // Filter future/ongoing events
            $filtered = array_filter($arr, function($e) use ($today) {
                $end = $e['end_date'] ?? $e['start_date'] ?? null;
                if (!$end) return false;
                return (strtotime($end) >= strtotime($today));
            });
            usort($filtered, function($a, $b){
                return strtotime($a['start_date'] ?? '2100-01-01') - strtotime($b['start_date'] ?? '2100-01-01');
            });
            $events = array_values($filtered);
        }
    }
}

// Group events by month (Month Year)
$eventsByMonth = [];
foreach ($events as $e) {
    if (empty($e['start_date'])) continue; // ignore malformed
    $monthKey = date('F Y', strtotime($e['start_date']));
    if (!isset($eventsByMonth[$monthKey])) $eventsByMonth[$monthKey] = [];
    $eventsByMonth[$monthKey][] = $e;
}

// Sort months in chronological order (earliest first)
uksort($eventsByMonth, function($a, $b){
    $ta = strtotime($a . ' 1');
    $tb = strtotime($b . ' 1');
    return $ta - $tb;
});

function category_label($cat) {
    $map = [
        'short_course' => 'Short Course',
        'certificate' => 'Professional Certificate',
        'odl' => 'Open & Distance',
        'micro_credential' => 'Micro-Credential',
        'apel' => 'APEL',
        'consultancy' => 'Consultancy',
    ];
    return $map[$cat] ?? ucwords(str_replace('_',' ', $cat));
}

function badge_class($cat) {
    return 'badge-' . ($cat ?: 'short_course');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Events Calendar — ACE</title>
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

    .month-divider {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--accent);
      margin: 2.5rem 0 1rem 0;
      padding-bottom: 0.35rem;
      border-bottom: 3px solid rgba(111,66,193,0.08);
    }

    .event-card { transition: transform 0.2s, box-shadow 0.2s; border-left: 4px solid transparent; }
    .event-card:hover { transform: translateX(5px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.12) !important; border-left-color: var(--accent); }
    .event-img { height:140px; object-fit:cover; background:#e9e9ee; border-radius:6px 0 0 6px; }

    /* badge colors (reuse conventions from programmes) */
    .badge-short_course { background: rgba(13,202,240,0.12); color: #0dcaf0; font-weight:600; }
    .badge-certificate { background: rgba(111,66,193,0.12); color: #6f42c1; font-weight:600; }
    .badge-odl { background: rgba(40,167,69,0.12); color: #28a745; font-weight:600; }
    .badge-micro_credential { background: rgba(253,126,20,0.12); color: #fd7e14; font-weight:600; }
    .badge-apel { background: rgba(13,110,253,0.12); color: #0d6efd; font-weight:600; }
    .badge-consultancy { background: rgba(111,66,193,0.06); color: #6f42c1; font-weight:600; }
  </style>
</head>
<body>
<?php require_once __DIR__ . '/partials/header.php'; ?>

<main class="container section-card py-5">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h3 class="mb-0">Events Calendar</h3>
      <small class="text-muted">Upcoming programmes and events</small>
    </div>
    <div>
      <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to home
      </a>
    </div>
  </div>

  <?php if (empty($eventsByMonth)): ?>
    <div class="alert alert-info">
      <i class="bi bi-info-circle me-2"></i>No upcoming events available.
    </div>
  <?php else: ?>
    <?php foreach ($eventsByMonth as $month => $monthEvents): ?>
      <div class="month-divider">
        <i class="bi bi-calendar-event me-2"></i><?= htmlspecialchars($month) ?>
        <small class="text-muted ms-3" style="font-size: 0.95rem; font-weight: 400;">(<?= count($monthEvents) ?> event<?= count($monthEvents) > 1 ? 's' : '' ?>)</small>
      </div>

      <div class="row g-4">
        <?php foreach ($monthEvents as $ev):
          $title = htmlspecialchars($ev['title'] ?? 'Untitled');
          $desc = htmlspecialchars(mb_strimwidth($ev['description'] ?? '', 0, 220, '...'));
          $start = isset($ev['start_date']) ? date('j F Y', strtotime($ev['start_date'])) : null;
          $end = isset($ev['end_date']) ? date('j F Y', strtotime($ev['end_date'])) : null;
          $img = !empty($ev['image_url']) ? htmlspecialchars($ev['image_url']) : null;
          $cat = $ev['programme_category'] ?? '';
          $catLabel = category_label($cat);
          $badge = badge_class($cat);
          $link = 'programme_details.php?id=' . urlencode($ev['programme_id'] ?? '');
        ?>
        <div class="col-12">
          <div class="bg-white rounded shadow-sm event-card">
            <div class="row g-0">
              <div class="col-md-3">
                <div class="event-img w-100 h-100" 
                     style="background-image: url('<?= $img ? $img : '/ACE/images/programme-default.jpg' ?>'); background-size: cover; background-position: center; min-height:140px;">
                </div>
              </div>
              <div class="col-md-9">
                <div class="p-3">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <span class="badge <?= $badge ?> mb-2" style="font-size:0.85rem;"><?= $catLabel ?></span>
                      <h5 class="mb-1" style="color:var(--accent)"><?= $title ?></h5>
                    </div>
                    <div class="text-end">
                      <?php if ($start && $end && $start !== $end): ?>
                        <span class="text-muted small"><i class="bi bi-calendar-event"></i> <?= $start ?> — <?= $end ?></span>
                      <?php elseif ($start): ?>
                        <span class="text-muted small"><i class="bi bi-calendar-event"></i> <?= $start ?></span>
                      <?php endif; ?>
                      <div class="mt-2">
                        <a href="<?= $link ?>" class="btn btn-primary btn-sm">View Details <i class="bi bi-arrow-right ms-1"></i></a>
                      </div>
                    </div>
                  </div>

                  <p class="text-muted mb-0" style="line-height:1.6;"><?= $desc ?></p>
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