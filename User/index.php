<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ACE | Advancement and Continuing Education</title>
    <meta name="description" content="ACE @ UniKL RCMP — Advancement and Continuing Education">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="front.css">

    <style>
        :root{
            --accent:#6f42c1;
            --accent-600:#5a35a8;
            --muted:#f4f4f7
        }

        body {
            font-family: 'Poppins', system-ui;
            margin:0;
            padding-top:78px;
            background-color:var(--muted);
        }

        header.navbar-fixed {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1100;
            background: rgba(255,255,255,0.92);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .hero {
            min-height: 68vh;
            background-image: url('/ACE/images/background.jpg');
            background-size: cover;
            background-position: center;
            display:flex;
            align-items:center;
            color:#fff;
        }
        .hero .overlay { 
            background: rgba(0,0,0,0.45); 
            padding:60px; 
            border-radius:8px; 
        }

        .newsletter-img {
            height:160px;
            object-fit:cover;
            border-radius:6px 6px 0 0;
        }

        .announcement-panel {
            max-height: 520px;
            overflow-y: auto;
            padding-right: 6px;
        }

        .announcement-card {
            transition: 0.2s;
            border-left: 4px solid transparent;
            cursor: pointer;
        }
        .announcement-card:hover {
            transform: translateY(-4px);
            border-left-color: var(--accent);
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15);
        }

        .badge-new {
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            animation: pulse 2s infinite;
            color: #fff;
            font-weight: 600;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .section-card {
            padding:48px 0;
        }

        .btn-primary{
            background-color:var(--accent) !important;
            border-color:var(--accent) !important;
        }
        .btn-primary:hover{
            background-color:var(--accent-600) !important;
        }

        .social-feed-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .tiktok-embed, .instagram-media {
            margin: 0 auto !important;
            max-width: 100% !important;
        }

        .social-icon {
            font-size: 1.5rem;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/partials/header.php'; ?>

<div id="devNoticeContainer" class="container mt-3" style="display:none; z-index:1101;">
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Heads up — site under development:</strong>
        This website is new and still under development; features and content may change.
        If you encounter a bug, please let us know via the <a href="service_inquiry.php">Contact</a> page or by email.
        <button type="button" class="btn-close" id="devNoticeClose" aria-label="Close"></button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('devNoticeContainer');
    const closeBtn = document.getElementById('devNoticeClose');

    if (!container) return;

    container.style.display = 'block';

    const AUTO_CLOSE_MS = 300000;
    let timeoutId = setTimeout(function () {
        container.style.display = 'none';
    }, AUTO_CLOSE_MS);

    closeBtn.addEventListener('click', function () {
        clearTimeout(timeoutId);
        container.style.display = 'none';
    });
});
</script>

<?php
$khunPath = realpath(__DIR__ . '/../khun.php');
$khunLoaded = false;
$newsletters = [];
$announcements = [];

if ($khunPath && file_exists($khunPath)) {
    require_once $khunPath;
    $khunLoaded = true;
}

if ($khunLoaded && isset($conn) && $conn instanceof mysqli) {
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

    $q1 = $conn->query("
        SELECT programme_id AS id, title, created_at, 'programme' AS type 
        FROM programmes 
        WHERE is_active = 1
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    if ($q1 && $q1->num_rows > 0) {
        while ($row = $q1->fetch_assoc()) $announcements[] = $row;
    }

    $q2 = $conn->query("
        SELECT newsletter_id AS id, title, created_at, 'newsletter' AS type 
        FROM newsletters 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    if ($q2 && $q2->num_rows > 0) {
        while ($row = $q2->fetch_assoc()) $announcements[] = $row;
    }

    usort($announcements, fn($a,$b)=>strtotime($b['created_at'])-strtotime($a['created_at']));
    $announcements = array_slice($announcements, 0, 5);

    $q = $conn->query("
        SELECT newsletter_id, title, summary, image_url, full_newsletter_url, DATE_FORMAT(created_at, '%Y-%m-%d') AS created_at 
        FROM newsletters 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    if ($q && $q->num_rows > 0) {
        while ($row = $q->fetch_assoc()) $newsletters[] = $row;
    }
}

$social_media = [];
if ($khunLoaded && isset($conn) && $conn instanceof mysqli) {
    $result = $conn->query("SELECT * FROM social_media_settings WHERE id=1");
    if ($result && $result->num_rows > 0) {
        $social_media = $result->fetch_assoc();
    }
}
?>

<!-- HERO -->
<section class="hero">
    <div class="container">
        <div class="overlay">
            <h1 class="display-6"><strong>ACE </strong>| Advancement and Continuing Education</h1>
            <p class="lead mb-4">Extending UniKL's Excellence to the Community & Organisation at every stage of learning</p>
            <a href="about.php" class="btn btn-primary btn-lg me-2">Get to know more about ACE</a>
            <a href="features.php" class="btn btn-outline-light btn-lg">View courses</a>
        </div>
    </div>
</section>

<!-- MAIN SPLIT SECTION -->
<section class="section-card">
    <div class="container">
        <div class="row g-4">

            <!-- LEFT: NEWSLETTERS -->
            <div class="col-lg-9">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="mb-0"><i class="bi bi-newspaper text-primary me-2"></i>Latest Newsletters</h4>
                    <a href="newsletters.php" class="btn btn-outline-secondary btn-sm">All newsletters</a>
                </div>

                <div class="row g-3">
                    <?php if (empty($newsletters)): ?>
                        <div class="alert alert-info">No newsletters available yet.</div>
                    <?php else: ?>
                        <?php foreach($newsletters as $n): ?>
                        <div class="col-md-4">
                            <div class="bg-white rounded shadow-sm h-100 d-flex flex-column overflow-hidden">
                                <div class="newsletter-img" 
                                     style="background-image:url('<?= $n['image_url'] ?: '/ACE/images/newsletter-default.jpg' ?>'); 
                                            background-size:cover; background-position:center;">
                                </div>
                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <h6 class="mb-1" style="color:var(--accent)">
                                        <?= htmlspecialchars($n['title']) ?>
                                    </h6>
                                    <small class="text-muted mb-2"><?= date("j M Y", strtotime($n['created_at'])) ?></small>
                                    <p class="flex-grow-1 mb-3 text-muted small">
                                        <?= htmlspecialchars(mb_strimwidth($n['summary'] ?? '', 0, 100, '...')) ?>
                                    </p>
                                    <div class="mt-auto">
                                        <a href="<?= $n['full_newsletter_url'] ?>" target="_blank" class="btn btn-primary btn-sm">Read more</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT: ANNOUNCEMENTS -->
            <div class="col-lg-3">
                <h5 class="mb-3">
                    <i class="bi bi-megaphone text-primary me-2"></i>Announcements
                </h5>

                <div class="announcement-panel">
                    <div class="row g-3">
                        <?php if (empty($announcements)): ?>
                            <div class="col-12">
                                <div class="alert alert-info small">No announcements available</div>
                            </div>
                        <?php else: ?>
                            <?php foreach(array_slice($announcements, 0, 5) as $ann):
                                $isNew = (time() - strtotime($ann['created_at'])) < (7*24*60*60);
                                $icon = $ann['type']=='programme' ? 'bi-book' : 'bi-newspaper';
                                $badgeColor = $ann['type']=='programme' ? 'primary' : 'success';
                                $url = $ann['type']=='programme'
                                    ? "programme_details.php?id={$ann['id']}"
                                    : "newsletters.php";
                            ?>
                            <div class="col-12">
                                <a href="<?= $url ?>" class="text-decoration-none">
                                    <div class="bg-white rounded shadow-sm p-2 announcement-card">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="badge bg-<?= $badgeColor ?>" style="font-size: 0.65rem;">
                                                <i class="bi <?= $icon ?>"></i>
                                            </span>
                                            <?php if ($isNew): ?>
                                                <span class="badge-new" style="font-size: 0.6rem; padding: 2px 6px;">NEW</span>
                                            <?php endif; ?>
                                        </div>

                                        <h6 class="mb-1" style="color:var(--accent); font-size: 0.85rem;">
                                            <?= htmlspecialchars(mb_strimwidth($ann['title'], 0, 50, '...')) ?>
                                        </h6>

                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?= date('j M Y', strtotime($ann['created_at'])) ?>
                                        </small>
                                    </div>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- SOCIAL MEDIA FEEDS SECTION -->
<section class="section-card bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h3 class="mb-2">
                <i class="bi bi-share social-icon text-primary me-2"></i>Follow Us on Social Media
            </h3>
            <p class="text-muted">Stay connected with our latest updates</p>
        </div>

        <div class="row g-4">
            <!-- TikTok Feed -->
            <div class="col-md-6">
                <div class="social-feed-card">
                    <h5 class="mb-4 d-flex align-items-center">
                        <i class="bi bi-tiktok me-2" style="color: #000;"></i>
                        Latest TikTok Videos
                    </h5>

                    <?php if (!empty($social_media['tiktok_video_1']) || !empty($social_media['tiktok_video_2'])): ?>
                    <div class="row g-3">
                        <?php if (!empty($social_media['tiktok_video_1'])):
                            $clean_url_1 = preg_replace('/\?.*/', '', $social_media['tiktok_video_1']);
                        ?>
                        <div class="col-12">
                            <blockquote class="tiktok-embed"
                                        cite="<?= htmlspecialchars($clean_url_1) ?>"
                                        data-video-id="<?= basename($clean_url_1) ?>"
                                        style="max-width: 605px; min-width: 325px;">
                                <section>
                                    <a target="_blank" href="<?= htmlspecialchars($clean_url_1) ?>">View on TikTok</a>
                                </section>
                            </blockquote>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($social_media['tiktok_video_2'])):
                            $clean_url_2 = preg_replace('/\?.*/', '', $social_media['tiktok_video_2']);
                        ?>
                        <div class="col-12">
                            <blockquote class="tiktok-embed"
                                        cite="<?= htmlspecialchars($clean_url_2) ?>"
                                        data-video-id="<?= basename($clean_url_2) ?>"
                                        style="max-width: 605px; min-width: 325px;">
                                <section>
                                    <a target="_blank" href="<?= htmlspecialchars($clean_url_2) ?>">View on TikTok</a>
                                </section>
                            </blockquote>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>No TikTok videos configured yet.
                    </div>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <a href="https://www.tiktok.com/@acercmp.official" target="_blank" class="btn btn-outline-dark btn-sm">
                            <i class="bi bi-tiktok me-1"></i>View More on TikTok
                        </a>
                    </div>
                </div>
            </div>

            <!-- Instagram / Facebook Feed -->
            <div class="col-md-6">
                <div class="social-feed-card">
                    <h5 class="mb-4 d-flex align-items-center">
                        <i class="bi bi-instagram me-2" style="color: #E1306C;"></i>
                        Latest Instagram Posts
                    </h5>

                    <?php if (!empty($social_media['instagram_post_1']) || !empty($social_media['facebook_post'])): ?>
                    <div class="row g-3">
                        <?php if (!empty($social_media['instagram_post_1'])): ?>
                        <div class="col-12">
                            <blockquote class="instagram-media"
                                        data-instgrm-permalink="<?= htmlspecialchars($social_media['instagram_post_1']) ?>"
                                        data-instgrm-version="14"
                                        style="max-width:540px; min-width:326px; width:99.375%;">
                            </blockquote>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($social_media['facebook_post'])): ?>
                        <div class="col-12">
                            <div class="fb-post" data-href="<?= htmlspecialchars($social_media['facebook_post']) ?>" data-width="540"></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>No Instagram or Facebook posts configured yet.
                    </div>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <a href="https://www.instagram.com/aceuniklrcmp" target="_blank" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-instagram me-1"></i>View More on Instagram
                        </a>
                        <a href="https://www.facebook.com/Ace.RCMP/" target="_blank" class="btn btn-outline-primary btn-sm ms-2">
                            <i class="bi bi-facebook me-1"></i>View More on Facebook
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script async src="https://www.tiktok.com/embed.js"></script>
<script async src="//www.instagram.com/embed.js"></script>

<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v16.0"></script>

<script>
(function(){
    const headerHeight = document.querySelector('header').offsetHeight;
    document.documentElement.style.scrollPaddingTop = headerHeight + 'px';
})();
</script>

</body>
</html>
