<?php

require_once __DIR__ . '/../security.php';
ace_set_security_headers();
ace_secure_session_start();
ace_require_https();
require_once __DIR__ . '/../khun.php';
require_once __DIR__ . '/../email_helper.php';

$success = false;
$error = '';
$info = '';

// Get service type from POST (preferred) or URL parameter, default to 'general'
$service_type = isset($_POST['inquiry_type']) && $_POST['inquiry_type'] !== ''
    ? $_POST['inquiry_type']
    : (isset($_GET['service']) ? $_GET['service'] : 'general');
$allowed_types = ['consultancy', 'specialized_course', 'coe', 'event_space_rental', 'general', 'short_course', 'micro_credential', 'certificate', 'apel', 'odl'];

// Validate service type
if (!in_array($service_type, $allowed_types)) {
    $service_type = 'general';
}

// COE options
$coe_options = [
    'HALAL CENTER OF EXCELLENCE' => 'HALAL CENTER OF EXCELLENCE',
    'CENTER FOR REGULATORY COMPLIANCE' => 'CENTER FOR REGULATORY COMPLIANCE',
    'CENTER FOR PRODUCT DEVELOPMENT & TOXICITY TESTING' => 'CENTER FOR PRODUCT DEVELOPMENT & TOXICITY TESTING',
    'CENTER OF LANGUAGE' => 'CENTER OF LANGUAGE',
    'PUSAT KAJIAN WARISAN & SEJARAH PERAK' => 'PUSAT KAJIAN WARISAN & SEJARAH PERAK'
];

// Service type labels
$service_labels = [
    'consultancy' => 'Consultancy Services',
    'specialized_course' => 'Specialized Course',
    'coe' => 'Center of Excellence Request',
    'event_space_rental' => 'Event Space Rental Request',
    'short_course' => 'Short Course Request',
    'micro_credential' => 'Micro-Credential Request',
    'certificate' => 'Professional Certificate Request',
    'apel' => 'APEL Request',
    'odl' => 'ODL Request',
    'general' => 'General Inquiry'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ace_csrf_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!ace_rate_limit('inquiry:' . $ip, 5, 600)) {
            $error = 'Too many submissions. Please wait a few minutes and try again.';
        } else {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $inquiry_type = $_POST['inquiry_type'] ?? 'general';
            $coe_centre = trim($_POST['coe_centre'] ?? '');
            $otp_code_input = trim($_POST['otp_code'] ?? '');

            // If COE is selected, append the centre to the subject
            if ($inquiry_type === 'coe' && !empty($coe_centre)) {
                $subject = $coe_centre . ' - ' . $subject;
            }

            if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                $error = 'Please fill in all required fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {
                // Two-factor via one-time code emailed to user
                $otp_verified = false;
                $session_otp = $_SESSION['inquiry_otp_code'] ?? null;
                $session_expiry = $_SESSION['inquiry_otp_expiry'] ?? 0;

                if ($otp_code_input === '') {
                    // Send a new code and ask user to verify
                    $otp = (string)random_int(100000, 999999);
                    $_SESSION['inquiry_otp_code'] = $otp;
                    $_SESSION['inquiry_otp_expiry'] = time() + 300; // 5 minutes

                    // Send OTP via Outlook SMTP using helper (with detailed error)
                    $result = send_inquiry_otp($email, $name, $otp);
                    if (is_array($result) ? $result['ok'] : (bool)$result) {
                        $info = 'We sent a verification code to your email (' . substr($email, 0, 3) . '***). Please enter it below to submit your inquiry.';
                    } else {
                        $errorDetail = is_array($result) && isset($result['error']) ? $result['error'] : 'Unknown error';
                        $error = 'Failed to send verification code: ' . htmlspecialchars($errorDetail);
                        unset($_SESSION['inquiry_otp_code'], $_SESSION['inquiry_otp_expiry']);
                    }
                } else {
                    if (!$session_otp || $session_expiry < time()) {
                        $error = 'Verification code expired. Please request a new code.';
                    } elseif ($otp_code_input !== $session_otp) {
                        $error = 'Invalid verification code. Please try again.';
                    } else {
                        $otp_verified = true;
                    }
                }

                if ($otp_verified) {
                    // Send email notification
                    $inquiry_data = [
                        'name' => htmlspecialchars($name),
                        'email' => htmlspecialchars($email),
                        'phone' => htmlspecialchars($phone ?: 'Not provided'),
                        'subject' => htmlspecialchars($subject),
                        'message' => nl2br(htmlspecialchars($message)),
                        'inquiry_type' => $inquiry_type,
                        'created_at' => date('F j, Y g:i A')
                    ];

                    if (send_inquiry_notification($inquiry_data)) {
                        $success = true;
                        // clear OTP session on success
                        unset($_SESSION['inquiry_otp_code'], $_SESSION['inquiry_otp_expiry']);
                        $_POST = []; // Clear form
                    } else {
                        $error = 'Failed to send inquiry notification. Please try again.';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($service_labels[$service_type]) ?> — ACE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="front.css">
    <style>
        :root{--accent:#6f42c1;--accent-600:#5a35a8;--muted:#f4f4f7}
        body { font-family: 'Poppins', sans-serif; padding-top:78px; background-color:var(--muted); }
        .btn-primary{ background-color:var(--accent) !important; border-color:var(--accent) !important; }
        .btn-primary:hover{ background-color:var(--accent-600) !important; }
        .page-header { background: linear-gradient(135deg, var(--accent) 0%, var(--accent-600) 100%); color:#fff; padding:60px 0; }
        .contact-card { background:#fff; border-radius:12px; padding:32px; box-shadow:0 4px 12px rgba(0,0,0,0.08); margin-bottom:24px; }
        .contact-info-item { display:flex; align-items-start; margin-bottom:24px; }
        .contact-info-item i { font-size:24px; color:var(--accent); margin-right:16px; margin-top:4px; }

        /* Small message style used for CoE selected centre */
        .coe-message { background: linear-gradient(135deg, rgba(111,66,193,0.1), rgba(111,66,193,0.05)); border-left:4px solid var(--accent); padding:16px; border-radius:8px; margin-bottom:16px; }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="display-5 mb-3">
                📧 Contact Us
        </h1>
        <p class="lead mb-0">
                Have questions? We'd love to hear from you. Send us a message!
        </p>
    </div>
</section>

<!-- Contact Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-8 mb-4">
                <div class="contact-card">
                    <h3 class="mb-4">Send Us a Message</h3>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Thank you!</strong> Your inquiry has been submitted successfully. We'll get back to you soon.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($info): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <?= htmlspecialchars($info) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <?= ace_csrf_input(); ?>
                        <div class="mb-3">
                            <label class="form-label">Service Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="inquiry_type" id="inquiry_type" required onchange="toggleCoeOptions()">
                                <option value="">Select service type...</option>
                                <?php foreach ($service_labels as $type => $label): ?>
                                <option value="<?= $type ?>" <?= ($service_type === $type) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- COE Centre Selection (Hidden by default) -->
                        <div class="mb-3" id="coe_centre_wrapper" style="display: none;">
                            <label class="form-label">Select Centre of Excellence <span class="text-danger">*</span></label>
                            <select class="form-select" name="coe_centre" id="coe_centre">
                                <option value="">Select a centre...</option>
                                <?php foreach ($coe_options as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>">
                                    <?= htmlspecialchars($label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="6" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="otp_code" class="form-label">Email Verification Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="otp_code" name="otp_code" maxlength="6" placeholder="Enter 6-digit code" value="<?= htmlspecialchars($_POST['otp_code'] ?? '') ?>">
                                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                            </div>
                            <small class="text-muted">Submit once to receive a 6-digit code via email, then enter it here and submit again.</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="col-lg-4">
                <div class="contact-card">
                    <h4 class="mb-4">Contact Information</h4>
                    
                    <div class="contact-info-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <div>
                            <h6 class="mb-1">Address</h6>
                            <p class="text-muted mb-0">Advance and Continuing Education (ACE)</p>
                            <p class="text-muted mb-0">Universiti Kuala Lumpur<br>Royal College of Medicine Perak (UniKL RCMP)
                                <br>3, Jalan Greentown<br>Ipoh, Perak 30450</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="bi bi-telephone-fill"></i>
                        <div>
                            <h6 class="mb-1">Phone</h6>
                            <p class="text-muted mb-0">1300 22 7267 / +605 226 3600</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="bi bi-envelope-fill"></i>
                        <div>
                            <h6 class="mb-1">Email</h6>
                            <p class="text-muted mb-0">ace.rcmp@unikl.edu.my</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="bi bi-clock-fill"></i>
                        <div>
                            <h6 class="mb-1">Office Hours</h6>
                            <p class="text-muted mb-0">Monday - Friday<br>8:00 AM - 5:00 PM</p>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">Follow Us</h6>
                    <div class="d-flex gap-2">
                        <a href="https://www.instagram.com/acercmp.official/?hl=en" class="btn btn-outline-primary btn-sm"><i class="bi bi-instagram"></i></a>
                        <a href="https://www.tiktok.com/@acercmp.official?_r=1&_t=ZS-91vXA4YDe91" class="btn btn-outline-primary btn-sm"><i class="bi bi-tiktok"></i></a>
                        <a href="https://www.facebook.com/Ace.RCMP/" class="btn btn-outline-primary btn-sm"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.youtube.com/@acercmp.official" class="btn btn-outline-primary btn-sm"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleCoeOptions() {
    const inquiryType = document.getElementById('inquiry_type').value;
    const coeWrapper = document.getElementById('coe_centre_wrapper');
    const coeSelect = document.getElementById('coe_centre');
    
    if (inquiryType === 'coe') {
        coeWrapper.style.display = 'block';
        coeSelect.setAttribute('required', 'required');
    } else {
        coeWrapper.style.display = 'none';
        coeSelect.removeAttribute('required');
        coeSelect.value = ''; // Clear selection
    }
}

// Run on page load to show COE options if COE was selected
document.addEventListener('DOMContentLoaded', function() {
    toggleCoeOptions();
});
</script>
</body>
</html>