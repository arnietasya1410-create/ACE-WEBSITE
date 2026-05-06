<?php
require_once __DIR__ . '/../admin/_inc.php';

$programme_id = isset($_GET['programme_id']) ? (int)$_GET['programme_id'] : 0;
$payment_method = isset($_GET['payment_method']) ? (int)$_GET['payment_method'] : 0;

if ($programme_id <= 0) {
    $_SESSION['flash'] = 'Invalid programme selected.';
    $_SESSION['flash_type'] = 'error';
    header('Location: /courses.php');
    exit;
}

$stmt = $conn->prepare('SELECT programme_id, form_url FROM programmes WHERE programme_id = ? AND is_active = 1 LIMIT 1');
$stmt->bind_param('i', $programme_id);
$stmt->execute();
$res = $stmt->get_result();
$programme = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
$stmt->close();

if (!$programme) {
    $_SESSION['flash'] = 'Programme not found.';
    $_SESSION['flash_type'] = 'error';
    header('Location: /courses.php');
    exit;
}

$formUrl = trim((string)($programme['form_url'] ?? ''));
if ($formUrl !== '') {
    $joiner = (strpos($formUrl, '?') !== false) ? '&' : '?';
    $target = $formUrl . $joiner . 'payment_method=' . urlencode((string)$payment_method);
    header('Location: ' . $target);
    exit;
}

$_SESSION['flash'] = 'Registration link is not available for this programme yet.';
$_SESSION['flash_type'] = 'error';
header('Location: /programme.php?id=' . $programme_id);
exit;
