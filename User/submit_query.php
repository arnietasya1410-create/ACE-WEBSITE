<?php
require_once __DIR__ . '/../admin/_inc.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ACE/User/courses.php');
    exit;
}

if (!ace_csrf_validate($_POST['csrf_token'] ?? '')) {
    $_SESSION['flash'] = 'Invalid request. Please try again.';
    $_SESSION['flash_type'] = 'error';
    header('Location: /ACE/User/courses.php');
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!ace_rate_limit('query:' . $ip, 5, 600)) {
    $_SESSION['flash'] = 'Too many submissions. Please wait a few minutes and try again.';
    $_SESSION['flash_type'] = 'error';
    header('Location: /ACE/User/courses.php');
    exit;
}

$programme_id = (int)($_POST['programme_id'] ?? 0);
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate required fields
if (!$programme_id || !$full_name || !$email || !$message) {
    $_SESSION['flash'] = 'Please fill in all required fields.';
    $_SESSION['flash_type'] = 'error';
    header("Location: programme.php?id=$programme_id");
    exit;
}

// Check programme exists and active
$stmt = $conn->prepare("SELECT programme_id FROM programmes WHERE programme_id = ? AND is_active = 1");
$stmt->bind_param('i', $programme_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close();
    $_SESSION['flash'] = 'Invalid programme selected.';
    $_SESSION['flash_type'] = 'error';
    header("Location: /ACE/User/courses.php");
    exit;
}
$stmt->close();

// Insert query
$stmt = $conn->prepare("
  INSERT INTO queries (full_name, email, phone, programme_id, message, submitted_at)
  VALUES (?, ?, ?, ?, ?, NOW())
");
// Fixed: 5 parameters = 's' (string), 's' (string), 's' (string), 'i' (integer), 's' (string)
$stmt->bind_param('sssis', $full_name, $email, $phone, $programme_id, $message);

if ($stmt->execute()) {
    $_SESSION['flash'] = '✅ Thank you for your inquiry! Your query has been submitted successfully. Our team will get back to you soon via email.';
    $_SESSION['flash_type'] = 'success';
    header("Location: programme.php?id=$programme_id");
} else {
    $_SESSION['flash'] = 'We encountered an issue while submitting your query. Please try again or contact us directly.';
    $_SESSION['flash_type'] = 'error';
    header("Location: programme.php?id=$programme_id");
}

$stmt->close();
exit;
?>
