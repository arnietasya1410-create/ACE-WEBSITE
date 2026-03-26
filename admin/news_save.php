<?php

require_once __DIR__ . '/_inc.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ACE/admin/news_edit.php');
    exit;
}

if (!ace_csrf_validate($_POST['csrf_token'] ?? '')) {
    flash_set('Invalid request. Please try again.');
    header('Location: news_edit.php' . (!empty($_POST['newsletter_id']) ? '?id=' . (int)$_POST['newsletter_id'] : ''));
    exit;
}
$admin_username = $_SESSION['admin_user'] ?? 'Admin';

// Validate required fields
$newsletter_id = isset($_POST['newsletter_id']) && $_POST['newsletter_id'] !== '' ? (int)$_POST['newsletter_id'] : null;
$title = trim($_POST['title'] ?? '');
$summary = trim($_POST['summary'] ?? '');
$full_newsletter_url = trim($_POST['full_newsletter_url'] ?? '');
$created_at = trim($_POST['created_at'] ?? ''); // Get the date input
$existing_image = trim($_POST['existing_image'] ?? '');
$remove_image = isset($_POST['remove_image']) ? 1 : 0;
$image_url = $existing_image; // Start with existing image

// Validate
if (empty($title)) {
    flash_set('Title is required.');
    header('Location: news_edit.php' . ($newsletter_id ? "?id=$newsletter_id" : ''));
    exit;
}

if (!ace_validate_url_or_empty($full_newsletter_url)) {
    flash_set('Full newsletter URL is invalid.');
    header('Location: news_edit.php' . ($newsletter_id ? "?id=$newsletter_id" : ''));
    exit;
}
if (empty($summary)) {
    flash_set('Summary is required.');
    header('Location: news_edit.php' . ($newsletter_id ? "?id=$newsletter_id" : ''));
    exit;
}
if (empty($created_at)) {
    flash_set('Publication date is required.');
    header('Location: news_edit.php' . ($newsletter_id ? "?id=$newsletter_id" : ''));
    exit;
}

// Validate date format
$date = DateTime::createFromFormat('Y-m-d', $created_at);
if (!$date || $date->format('Y-m-d') !== $created_at) {
    flash_set('Invalid date format.');
    header('Location: news_edit.php' . ($newsletter_id ? "?id=$newsletter_id" : ''));
    exit;
}

// Check if date is not in the future
if ($date > new DateTime()) {
    flash_set('Publication date cannot be in the future.');
    header('Location: news_edit.php' . ($newsletter_id ? "?id=$newsletter_id" : ''));
    exit;
}

// Handle image upload
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['image_file'];

    if (!ace_validate_image_upload($file)) {
        flash_set('Invalid image format or size. Please use JPG, PNG or GIF (max 5MB).');
        header('Location: news_edit.php' . ($newsletter_id ? "?id=$newsletter_id" : ''));
        exit;
    }

    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/newsletters';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            flash_set('Failed to create upload directory.');
            header('Location: news_edit.php' . ($newsletter_id ? "?id=$newsletter_id" : ''));
            exit;
        }
    }

    // Generate unique filename
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'newsletter_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $filepath = $uploadDir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Store relative path for DB
        $image_url = '/ACE/uploads/newsletters/' . $filename;
    } else {
        flash_set('Failed to upload image.');
        header('Location: news_edit.php' . ($newsletter_id ? "?id=$newsletter_id" : ''));
        exit;
    }
}

// Remove image if checked
if ($remove_image) {
    $image_url = null;
}

// DB operations
if (!isset($conn) || !($conn instanceof mysqli)) {
    flash_set('Database connection failed.');
    header('Location: news_edit.php' . ($newsletter_id ? "?id=$newsletter_id" : ''));
    exit;
}

$success = false;
$admin_id = $_SESSION['admin_id'] ?? 0;

if ($newsletter_id) {
    // UPDATE existing
    $stmt = $conn->prepare(
        "UPDATE newsletters SET title=?, summary=?, image_url=?, full_newsletter_url=?, created_at=? WHERE newsletter_id=?"
    );
    if ($stmt) {
        $stmt->bind_param('sssssi', $title, $summary, $image_url, $full_newsletter_url, $created_at, $newsletter_id);
        if ($stmt->execute()) {
            $success = true;
            
            // LOG THE UPDATE
            log_newsletter_updated($newsletter_id, $title);
            
            flash_set('Newsletter updated successfully.');
        } else {
            flash_set('Failed to update newsletter: ' . $conn->error);
        }
        $stmt->close();
    }
} else {
    // INSERT new
    $stmt = $conn->prepare(
        "INSERT INTO newsletters (title, summary, image_url, full_newsletter_url, created_by, created_at) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    if ($stmt) {
        $stmt->bind_param('ssssis', $title, $summary, $image_url, $full_newsletter_url, $admin_id, $created_at);
        if ($stmt->execute()) {
            $success = true;
            $newsletter_id = $conn->insert_id;
            
            // LOG THE CREATION
            log_newsletter_created($newsletter_id, $title);
            
            flash_set('Newsletter created successfully.');
        } else {
            flash_set('Failed to create newsletter: ' . $conn->error);
        }
        $stmt->close();
    }
}

if ($success) {
    header('Location: /ACE/admin/news_edit.php?id=' . $newsletter_id);
} else {
    header('Location: news_edit.php' . ($newsletter_id ? "?id=$newsletter_id" : ''));
}
exit;
?>