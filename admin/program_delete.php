<?php
require_once __DIR__ . '/_inc.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: program_list.php');
    exit;
}

if (!ace_csrf_validate($_POST['csrf_token'] ?? '')) {
    flash_set('Invalid request. Please try again.');
    header('Location: program_list.php');
    exit;
}

$programme_id = isset($_POST['programme_id']) ? (int)$_POST['programme_id'] : 0;

if (!$programme_id) {
    flash_set('Invalid programme ID.');
    header('Location: dashboard.php');
    exit;
}

// 1. Delete programme images from filesystem and DB
$stmt = $conn->prepare("SELECT filename FROM programme_images WHERE programme_id = ?");
$stmt->bind_param('i', $programme_id);
$stmt->execute();
$res = $stmt->get_result();

$upload_dir = __DIR__ . '/../uploads/programmes/';

while ($row = $res->fetch_assoc()) {
    $safe_name = basename($row['filename']);
    $file_path = $upload_dir . $safe_name;
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}
$stmt->close();

$conn->query("DELETE FROM programme_images WHERE programme_id = $programme_id");

// 2. Delete payment methods associations
$conn->query("DELETE FROM programme_payment_methods WHERE programme_id = $programme_id");

// 3. Delete the programme itself
// Get programme title before deleting (for logging)
$stmt_title = $conn->prepare("SELECT title FROM programmes WHERE programme_id = ?");
$stmt_title->bind_param('i', $programme_id);
$stmt_title->execute();
$result_title = $stmt_title->get_result();
$programme = $result_title->fetch_assoc();
$programme_title = $programme['title'] ?? 'Unknown Programme';
$stmt_title->close();

$stmt = $conn->prepare("DELETE FROM programmes WHERE programme_id = ?");
$stmt->bind_param('i', $programme_id);

if ($stmt->execute()) {
    // LOG THE DELETION
    log_programme_deleted($programme_id, $programme_title);
    
    $_SESSION['flash'] = 'Programme deleted successfully!';
    $_SESSION['flash_type'] = 'success';
} else {
    $_SESSION['flash'] = 'Failed to delete programme.';
    $_SESSION['flash_type'] = 'danger';
}

$stmt->close();
    header('Location: program_list.php');
exit;
?>
