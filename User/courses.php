<?php
require_once __DIR__ . '/../khun.php';
session_start();

// Fetch only short course programmes with first image
$programmes = [];
if (isset($conn) && $conn instanceof mysqli) {
    $stmt = $conn->query("SELECT p.programme_id, p.title, p.description, p.start_date, p.end_date, 
                                 p.location, p.price, p.programme_category, p.has_packages,
                                 (SELECT filename FROM programme_images WHERE programme_id = p.programme_id LIMIT 1) as first_image
                          FROM programmes p
                          WHERE p.is_active = 1 AND p.programme_category = 'short_course'
                          ORDER BY p.created_at DESC");
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            $programmes[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Short Courses — ACE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="front.css">
    <style>
        :root{--accent:#6f42c1;--accent-600:#5a35a8;--muted:#f4f4f7}
        body { font-family: 'Poppins', sans-serif; padding-top:78px; background-color:var(--muted); }
        .btn-primary{ background-color:var(--accent) !important; border-color:var(--accent) !important; }
        .btn-primary:hover{ background-color:var(--accent-600) !important; }
        .page-header { background: linear-gradient(135deg, var(--accent) 0%, var(--accent-600) 100%); color:#fff; padding:48px 0; }
        .programme-row { background:#fff; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.08); margin-bottom:20px; transition: transform 0.3s; }
        .programme-row:hover { transform: translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,0.12); }
        .programme-image { width:200px; height:150px; object-fit:cover; border-radius:8px 0 0 8px; cursor: pointer; }
        .badge-short_course { background: rgba(13,202,240,0.12); color: #0dcaf0; font-weight:600; }
        .badge-certificate { background: rgba(111,66,193,0.12); color: #6f42c1; font-weight:600; }
        .badge-odl { background: rgba(40,167,69,0.12); color: #28a745; font-weight:600; }
        .badge-micro_credential { background: rgba(253,126,20,0.12); color: #fd7e14; font-weight:600; }
        .badge-apel { background: rgba(13,110,253,0.12); color: #0d6efd; font-weight:600; }
        .modal-body img { width: 100%; height: auto; }
        .zoom-controls { position: absolute; top: 10px; right: 10px; z-index: 1060; }
        .zoom-controls .btn { margin-left: 5px; }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="display-5 mb-3">📚 Short Courses</h1>
        <p class="lead mb-0">Browse our collection of short training courses.</p>
    </div>
</section>

<!-- Quick request CTA -->
<section class="container mt-3">
    <div class="alert alert-info">
        Can't find the short course you need? <a href="service_inquiry.php?service=short_course" class="alert-link">Request a Short Course</a> and we'll assist you.
    </div>
</section>

<!-- Programmes List -->
<section class="py-5">
    <div class="container">
        <?php if (empty($programmes)): ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i>
            No programmes available at the moment. Please check back later.
        </div>
        <?php else: ?>
        <div class="list-group">
            <?php foreach ($programmes as $prog): 
                $category_label = ucwords(str_replace('_', ' ', $prog['programme_category'] ?? 'course'));
                $badge_class = 'badge-' . ($prog['programme_category'] ?? 'short_course');
            ?>
            <div class="programme-row d-flex align-items-stretch mb-3 p-0 overflow-hidden">
                <div class="d-none d-md-block" style="width:200px;">
                    <?php if (!empty($prog['first_image'])): ?>
                    <img src="<?= htmlspecialchars($prog['first_image']) ?>" 
                         class="programme-image w-100 h-100" 
                         alt="<?= htmlspecialchars($prog['title']) ?>"
                         id="progImg<?= $prog['programme_id'] ?>">
                    <?php else: ?>
                    <div class="programme-image w-100 h-100 bg-secondary d-flex align-items-center justify-content-center text-white">
                        <i class="bi bi-book" style="font-size:48px;"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex-grow-1 p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge <?= $badge_class ?> mb-2"><?= htmlspecialchars($category_label) ?></span>
                            <h5 class="mb-2"><?= htmlspecialchars($prog['title']) ?></h5>
                        </div>
                        <?php if (!empty($prog['has_packages'])): ?>
                        <div class="text-end">
                            <small class="text-muted">Refer to Programme description</small>
                        </div>
                        <?php elseif (!empty($prog['price'])): ?>
                        <div class="text-end">
                            <strong class="h5 text-primary">RM <?= number_format($prog['price'], 2) ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-muted mb-3">
                        <?= htmlspecialchars(substr($prog['description'] ?? '', 0, 200)) ?><?= strlen($prog['description'] ?? '') > 200 ? '...' : '' ?>
                    </p>
                    
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <?php if (!empty($prog['start_date']) && !empty($prog['end_date'])): ?>
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i> <?= date('M d', strtotime($prog['start_date'])) ?> - <?= date('M d, Y', strtotime($prog['end_date'])) ?>
                        </small>
                        <?php endif; ?>
                        
                        <?php if (!empty($prog['location'])): ?>
                        <small class="text-muted">
                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($prog['location']) ?>
                        </small>
                        <?php endif; ?>
                      
                        
                        <a href="programme_details.php?id=<?= $prog['programme_id'] ?>" class="btn btn-primary btn-sm ms-auto">
                            View Details <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Programme Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body position-relative p-0">
                <div class="zoom-controls">
                    <button type="button" class="btn btn-light btn-sm" id="zoomIn"><i class="bi bi-zoom-in"></i></button>
                    <button type="button" class="btn btn-light btn-sm" id="zoomOut"><i class="bi bi-zoom-out"></i></button>
                    <button type="button" class="btn btn-light btn-sm" id="resetZoom"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
                <div style="overflow: auto; max-height: 80vh;">
                    <img id="modalImage" src="" alt="" style="transform-origin: center; transition: transform 0.3s;">
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let currentZoom = 1;
    const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
    
    function showImage(imageSrc, imageTitle) {
        const modalImage = document.getElementById('modalImage');
        const modalTitle = document.getElementById('imageModalLabel');
        
        modalImage.src = imageSrc;
        modalImage.alt = imageTitle;
        modalTitle.textContent = imageTitle;
        currentZoom = 1;
        modalImage.style.transform = 'scale(1)';
        
        imageModal.show();
    }
    
    document.getElementById('zoomIn').addEventListener('click', function() {
        currentZoom += 0.2;
        document.getElementById('modalImage').style.transform = `scale(${currentZoom})`;
    });
    
    document.getElementById('zoomOut').addEventListener('click', function() {
        if (currentZoom > 0.4) {
            currentZoom -= 0.2;
            document.getElementById('modalImage').style.transform = `scale(${currentZoom})`;
        }
    });
    
    document.getElementById('resetZoom').addEventListener('click', function() {
        currentZoom = 1;
        document.getElementById('modalImage').style.transform = 'scale(1)';
    });
</script>
</body>
</html>