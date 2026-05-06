<?php
require_once __DIR__ . '/../admin/_inc.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id || !($conn instanceof mysqli)) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID or DB connection']);
    exit;
}

// Get programme info
$stmt = $conn->prepare("SELECT programme_id, title, description, start_date, end_date, location, price, has_packages FROM programmes WHERE programme_id = ? AND is_active = 1 LIMIT 1");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed']);
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Programme not found']);
    exit;
}

$programme = $res->fetch_assoc();
$stmt->close();

// Get images
$stmt2 = $conn->prepare("SELECT image_id, filename FROM programme_images WHERE programme_id = ?");
$stmt2->bind_param('i', $id);
$stmt2->execute();
$res2 = $stmt2->get_result();

$images = [];
while ($row = $res2->fetch_assoc()) {
    $filename = $row['filename'];
    
    // Remove leading slash if it exists, then add it back to ensure single slash
    $filename = ltrim($filename, '/');
    $full_path = '/' . $filename;
    
    $images[] = [
        'image_id' => $row['image_id'],
        'filename' => $filename,
        'full_path' => $full_path
    ];
}
$stmt2->close();

$programme['images'] = $images;

echo json_encode(['success' => true, 'programme' => $programme]);
?>