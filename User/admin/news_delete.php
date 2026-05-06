<?php

require_once __DIR__ . '/_inc.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: news_edit.php');
    exit;
}

if (!ace_csrf_validate($_POST['csrf_token'] ?? '')) {
    flash_set('Invalid request. Please try again.');
    header('Location: news_edit.php');
    exit;
}

$newsletter_id = isset($_POST['newsletter_id']) ? (int)$_POST['newsletter_id'] : 0;

if ($newsletter_id <= 0) {
    flash_set('Invalid newsletter ID.');
    header('Location: news_edit.php');
    exit;
}

// Get newsletter details to delete associated image AND for logging
$stmt = $conn->prepare("SELECT title, image_url FROM newsletters WHERE newsletter_id = ?");
if ($stmt) {
    $stmt->bind_param('i', $newsletter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $newsletter = $result->fetch_assoc();
    $stmt->close();

    if ($newsletter) {
        $newsletter_title = $newsletter['title'];
        
        // Delete the newsletter from database
        $stmt = $conn->prepare("DELETE FROM newsletters WHERE newsletter_id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $newsletter_id);
            if ($stmt->execute()) {
                // Delete associated image file if exists
                if (!empty($newsletter['image_url'])) {
                    $safe_name = basename($newsletter['image_url']);
                    $imagePath = __DIR__ . '/../uploads/newsletters/' . $safe_name;
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                // LOG THE DELETION
                log_newsletter_deleted($newsletter_id, $newsletter_title);
                
                flash_set('Newsletter deleted successfully.');
            } else {
                flash_set('Failed to delete newsletter: ' . $conn->error);
            }
            $stmt->close();
        }
    } else {
        flash_set('Newsletter not found.');
    }
}

header('Location: news_edit.php');
exit;
?>