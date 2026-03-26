<?php
require_once __DIR__ . '/../khun.php';
session_start();

$programme_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$programme_id || !($conn instanceof mysqli)) {
    header('Location: courses.php');
    exit;
}

// Get programme info
$stmt = $conn->prepare("SELECT programme_id, title, description, start_date, end_date, location, price, form_url, programme_category, has_packages, person_in_charge 
                        FROM programmes 
                        WHERE programme_id = ? AND is_active = 1 LIMIT 1");
$stmt->bind_param('i', $programme_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header('Location: courses.php');
    exit;
}

$programme = $res->fetch_assoc();
$stmt->close();

// Get images from programme_images table
$stmt2 = $conn->prepare("SELECT image_id, filename FROM programme_images WHERE programme_id = ? ORDER BY image_id ASC");
$stmt2->bind_param('i', $programme_id);
$stmt2->execute();
$res2 = $stmt2->get_result();

$images = [];
while ($row = $res2->fetch_assoc()) {
    $images[] = $row;
}
$stmt2->close();

// Get available payment methods for this programme with messages
$stmt3 = $conn->prepare("
    SELECT pm.payment_method_id, pm.label, ppm.message
    FROM programme_payment_methods ppm
    JOIN payment_methods pm ON ppm.payment_method_id = pm.payment_method_id
    WHERE ppm.programme_id = ?
    ORDER BY pm.label ASC
");
$stmt3->bind_param('i', $programme_id);
$stmt3->execute();
$res3 = $stmt3->get_result();

$payment_methods = [];
while ($row = $res3->fetch_assoc()) {
    $payment_methods[] = $row;
}
$stmt3->close();

$category_names = [
    'short_course' => 'Short Course',
    'certificate' => 'Professional Certificate',
    'odl' => 'Open and Distance Learning (ODL)',
    'micro_credential' => 'Micro-Credential',
    'apel' => 'APEL'
];

$category_label = $category_names[$programme['programme_category']] ?? 'Programme';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($programme['title']) ?> — ACE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="front.css">
    <style>
        :root{--accent:#6f42c1;--accent-600:#5a35a8;--muted:#f4f4f7}
        body { font-family: 'Poppins', sans-serif; padding-top:78px; background-color:var(--muted); }
        .btn-primary{ background-color:var(--accent) !important; border-color:var(--accent) !important; }
        .btn-primary:hover{ background-color:var(--accent-600) !important; }
        .programme-image { width:100%; height:500px; object-fit:contain; background:#000; border-radius:8px; cursor: pointer; }
        .info-card { background:#fff; border-radius:8px; padding:24px; box-shadow:0 4px 12px rgba(0,0,0,0.08); }
        .badge-category { background: rgba(111,66,193,0.12); color: var(--accent); font-weight:600; font-size:14px; }
        .modal-body img { width: 100%; height: auto; }
        .zoom-controls { position: absolute; top: 10px; right: 10px; z-index: 1060; }
        .zoom-controls .btn { margin-left: 5px; }
        .payment-message-box {
            background: linear-gradient(135deg, rgba(111,66,193,0.1), rgba(111,66,193,0.05));
            border-left: 4px solid var(--accent);
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
            animation: slideIn 0.3s ease-out;
            display: none;
        }
        .payment-message-box.show {
            display: block;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="courses.php">Programmes</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($programme['title']) ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Left Column: Images & Description -->
            <div class="col-lg-8 mb-4">
                <!-- Image Carousel -->
                <?php if (!empty($images)): ?>
                <div id="programmeCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <?php foreach ($images as $idx => $img): ?>
                        <button type="button" data-bs-target="#programmeCarousel" data-bs-slide-to="<?= $idx ?>" 
                                <?= $idx === 0 ? 'class="active"' : '' ?>></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="carousel-inner">
                        <?php foreach ($images as $idx => $img): ?>
                        <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                            <img src="<?= htmlspecialchars($img['filename']) ?>" 
                                 class="programme-image" 
                                 alt="<?= htmlspecialchars($programme['title']) ?>"
                                 onclick="showImage('<?= htmlspecialchars($img['filename']) ?>', '<?= htmlspecialchars(addslashes($programme['title'])) ?>')">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#programmeCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#programmeCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="bg-secondary text-white d-flex align-items-center justify-content-center mb-4" style="height:500px; border-radius:8px;">
                    <div class="text-center">
                        <i class="bi bi-image" style="font-size:80px;"></i>
                        <p class="mt-3">No images available</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <div class="info-card mb-4">
                    <span class="badge badge-category mb-3"><?= htmlspecialchars($category_label) ?></span>
                    <h2 class="mb-3"><?= htmlspecialchars($programme['title']) ?></h2>
                    
                    <h5 class="mt-4 mb-3">Programme Description</h5>
                    <p class="text-muted" style="white-space: pre-wrap;"><?= htmlspecialchars($programme['description'] ?? 'No description available.') ?></p>
                </div>
            </div>

            <!-- Right Column: Details & Registration -->
            <div class="col-lg-4">
                <div class="info-card sticky-top" style="top:90px; max-height: calc(100vh - 110px); overflow-y: auto;">
                    <h4 class="mb-4">Programme Details</h4>
                    
                    <?php if (!empty($programme['has_packages'])): ?>
                    <div class="mb-3">
                        <h5 class="text-primary mb-0">Refer to Programme description</h5>
                        <small class="text-muted">Multiple payment packages available</small>
                    </div>
                    <hr>
                    <?php elseif (!empty($programme['price'])): ?>
                    <div class="mb-3">
                        <h5 class="text-primary mb-0">RM <?= number_format($programme['price'], 2) ?></h5>
                        <small class="text-muted">Programme Fee</small>
                    </div>
                    <hr>
                    <?php endif; ?>
                    <?php if (!empty($programme['start_date']) && !empty($programme['end_date'])): ?>
                    <div class="mb-3">
                        <i class="bi bi-calendar-event text-primary me-2"></i>
                        <strong>Duration:</strong><br>
                        <small class="ms-4"><?= date('F d, Y', strtotime($programme['start_date'])) ?> 
                        to <?= date('F d, Y', strtotime($programme['end_date'])) ?></small>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($programme['location'])): ?>
                    <div class="mb-3">
                        <i class="bi bi-geo-alt text-primary me-2"></i>
                        <strong>Location:</strong><br>
                        <small class="ms-4"><?= htmlspecialchars($programme['location']) ?></small>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <!-- Payment Method Selection -->
                    <?php if (!empty($payment_methods)): ?>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label fw-bold">
                            <i class="bi bi-credit-card text-primary me-2"></i>Payment Method
                        </label>
                        <select class="form-select" id="payment_method" onchange="showPaymentMessage()">
                            <option value="">Select payment method...</option>
                            <?php foreach ($payment_methods as $pm): ?>
                            <option value='<?= json_encode(['label' => $pm['label'], 'message' => $pm['message']]) ?>'>
                                <?= htmlspecialchars($pm['label']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Please note that this payment method are only for information purpose, no payment are required to be made on this platform.</small>
                    </div>
                    
                    <!-- Payment Message Box (Hidden by default) -->
                    <div class="payment-message-box" id="paymentMessageBox">
                        <h6 class="fw-bold mb-2">
                            <i class="bi bi-info-circle text-primary me-2"></i><span id="paymentMethodName"></span>
                        </h6>
                        <p class="mb-0" style="white-space: pre-wrap;" id="paymentMessage"></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($programme['person_in_charge'])): ?>
                    <hr>
                    <div class="mb-3">
                        <h6 class="fw-bold mb-2">
                            <i class="bi bi-person-badge text-primary me-2"></i>Person In Charge
                        </h6>
                        <p class="mb-0 text-muted" style="white-space: pre-wrap;"><?= htmlspecialchars($programme['person_in_charge']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($programme['form_url'])): ?>
                    <a href="<?= htmlspecialchars($programme['form_url']) ?>" target="_blank" class="btn btn-primary btn-lg w-100 mb-2">
                        <i class="bi bi-pencil-square"></i> Register Now!
                    </a>
                    <small class="text-muted d-block text-center mb-3">
                        <i class="bi bi-info-circle"></i> Opens in new tab
                    </small>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Registration link not available yet. Please contact us.
                    </div>
                    <?php endif; ?>
                    
                    <a href="courses.php" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-left"></i> Back to All Programmes
                    </a>
                </div>
                </div>
            </div>
        </div>
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
                    <button type="button" class="btn btn-light btn-sm" id="zoomIn"><i class="bi bi-zoom-in"></i> Zoom In</button>
                    <button type="button" class="btn btn-light btn-sm" id="zoomOut"><i class="bi bi-zoom-out"></i> Zoom Out</button>
                    <button type="button" class="btn btn-light btn-sm" id="resetZoom"><i class="bi bi-arrow-clockwise"></i> Reset</button>
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
    
    function showPaymentMessage() {
        const select = document.getElementById('payment_method');
        const messageBox = document.getElementById('paymentMessageBox');
        const methodName = document.getElementById('paymentMethodName');
        const message = document.getElementById('paymentMessage');
        
        if (select.value === '') {
            messageBox.classList.remove('show');
            return;
        }
        
        try {
            const data = JSON.parse(select.value);
            methodName.textContent = data.label;
            message.textContent = data.message || 'No additional information available.';
            messageBox.classList.add('show');
        } catch (e) {
            console.error('Error parsing payment method data:', e);
            messageBox.classList.remove('show');
        }
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