<?php
// filepath: c:\xampp1\htdocs\ACE\admin\social_media.php
require_once __DIR__ . '/_inc.php';
require_admin(); // Allows both admin and super_admin

$admin_username = $_SESSION['admin_user'] ?? 'Admin';
$admin_id = $_SESSION['admin_id'] ?? 0;
$is_super_admin = isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ace_csrf_validate($_POST['csrf_token'] ?? '')) {
        flash_set('Invalid request. Please try again.');
        header('Location: social_media.php');
        exit;
    }

    $delete_field = $_POST['delete_field'] ?? '';
    $allowed_fields = ['tiktok_video_1', 'tiktok_video_2', 'instagram_post_1', 'instagram_post_2', 'facebook_post'];

    if ($delete_field !== '' && in_array($delete_field, $allowed_fields, true)) {
        $stmt = $conn->prepare("UPDATE social_media_settings SET {$delete_field}='', updated_by=?, updated_at=CURRENT_TIMESTAMP WHERE id=1");
        $stmt->bind_param('i', $admin_id);

        if ($stmt->execute()) {
            flash_set('Post removed successfully!');
        }
        $stmt->close();
        header('Location: social_media.php');
        exit;
    }

    // Clean TikTok URLs - remove query parameters
    $tiktok_video_1 = trim($_POST['tiktok_video_1'] ?? '');
    if (!empty($tiktok_video_1)) {
        $tiktok_video_1 = preg_replace('/\?.*/', '', $tiktok_video_1);
    }
    
    $tiktok_video_2 = trim($_POST['tiktok_video_2'] ?? '');
    if (!empty($tiktok_video_2)) {
        $tiktok_video_2 = preg_replace('/\?.*/', '', $tiktok_video_2);
    }
    
    $instagram_post_1 = trim($_POST['instagram_post_1'] ?? '');
    $instagram_post_2 = trim($_POST['instagram_post_2'] ?? '');
    $facebook_post = trim($_POST['facebook_post'] ?? '');
    if (!empty($facebook_post)) {
        // Remove query parameters to keep the stored URL tidy
        $facebook_post = preg_replace('/\?.*/', '', $facebook_post);
    }
    
    $urls = [
        'TikTok Video 1' => $tiktok_video_1,
        'TikTok Video 2' => $tiktok_video_2,
        'Instagram Post 1' => $instagram_post_1,
        'Instagram Post 2' => $instagram_post_2,
        'Facebook Post' => $facebook_post
    ];
    foreach ($urls as $label => $url) {
        if (!ace_validate_url_or_empty($url)) {
            flash_set($label . ' URL is invalid.');
            header('Location: social_media.php');
            exit;
        }
    }

    // Update or insert settings
    $stmt = $conn->prepare("
        INSERT INTO social_media_settings (id, tiktok_video_1, tiktok_video_2, instagram_post_1, instagram_post_2, facebook_post, updated_by) 
        VALUES (1, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            tiktok_video_1=VALUES(tiktok_video_1), 
            tiktok_video_2=VALUES(tiktok_video_2), 
            instagram_post_1=VALUES(instagram_post_1), 
            instagram_post_2=VALUES(instagram_post_2),
            facebook_post=VALUES(facebook_post),
            updated_by=VALUES(updated_by),
            updated_at=CURRENT_TIMESTAMP
    ");
    $stmt->bind_param('sssssi', $tiktok_video_1, $tiktok_video_2, $instagram_post_1, $instagram_post_2, $facebook_post, $admin_id);
    
    if ($stmt->execute()) {
        flash_set('Social media posts updated successfully!');
    } else {
        flash_set('Failed to update: ' . $conn->error);
    }
    $stmt->close();
    
    header('Location: social_media.php');
    exit;
}

// Load current settings
$result = $conn->query("SELECT * FROM social_media_settings WHERE id=1");
$settings = $result ? $result->fetch_assoc() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Social Media Settings — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f5f5f7; font-family: system-ui, -apple-system, sans-serif; }
        .content-wrapper { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .card { border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-radius: 12px; }
        .card-header { background: linear-gradient(135deg, #6f42c1, #5a35a8); color: #fff; border-radius: 12px 12px 0 0 !important; }
        .help-text { font-size: 0.875rem; color: #6c757d; margin-top: 5px; }
        .preview-box { background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; padding: 20px; margin-top: 10px; }
        .btn-primary { background: #6f42c1; border-color: #6f42c1; }
        .btn-primary:hover { background: #5a35a8; border-color: #5a35a8; }
        .alert { border-radius: 8px; }
        .current-post { background: #e7f3ff; border-left: 4px solid #0d6efd; padding: 12px; border-radius: 6px; margin-bottom: 10px; }
        .input-group-delete { position: relative; }
        .btn-delete-post { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10; }
    </style>
</head>
<body>

<?php 
// Load correct header based on role
if ($is_super_admin) {
    require_once __DIR__ . '/partials/header_super.php';
} else {
    require_once __DIR__ . '/partials/header.php';
}
?>

<div class="content-wrapper">
    <?php if ($msg = flash_get()): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="bi bi-share me-2"></i>Social Media Settings
            </h4>
        </div>
        <div class="card-body p-4">
            <p class="text-muted mb-4">
                <i class="bi bi-info-circle me-1"></i>
                You can update posts individually - leave a field empty if you don't want to change it, or clear it to remove the post.
            </p>

            <form method="POST" action="">
                <?= ace_csrf_input(); ?>
                <!-- TikTok Section -->
                <div class="mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-tiktok me-2"></i>TikTok Videos
                    </h5>

                    <div class="mb-3">
                        <label for="tiktok_video_1" class="form-label fw-bold">
                            TikTok Video 1 URL
                            <?php if (!empty($settings['tiktok_video_1'])): ?>
                                <span class="badge bg-success">Active</span>
                            <?php endif; ?>
                        </label>
                        
                        <?php if (!empty($settings['tiktok_video_1'])): ?>
                        <div class="current-post">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block mb-1">Current:</small>
                                    <a href="<?= htmlspecialchars($settings['tiktok_video_1']) ?>" target="_blank" class="text-break">
                                        <?= htmlspecialchars($settings['tiktok_video_1']) ?>
                                    </a>
                                </div>
                                <button type="submit"
                                        name="delete_field"
                                        value="tiktok_video_1"
                                        class="btn btn-danger btn-sm ms-2"
                                        onclick="return confirm('Remove this TikTok video?')">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <input type="url" 
                               class="form-control" 
                               id="tiktok_video_1" 
                               name="tiktok_video_1" 
                               value="<?= htmlspecialchars($settings['tiktok_video_1'] ?? '') ?>"
                               placeholder="https://www.tiktok.com/@username/video/1234567890">
                        <div class="help-text">
                            <i class="bi bi-lightbulb me-1"></i>
                            Paste new URL to update, or clear to remove
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tiktok_video_2" class="form-label fw-bold">
                            TikTok Video 2 URL
                            <?php if (!empty($settings['tiktok_video_2'])): ?>
                                <span class="badge bg-success">Active</span>
                            <?php endif; ?>
                        </label>
                        
                        <?php if (!empty($settings['tiktok_video_2'])): ?>
                        <div class="current-post">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block mb-1">Current:</small>
                                    <a href="<?= htmlspecialchars($settings['tiktok_video_2']) ?>" target="_blank" class="text-break">
                                        <?= htmlspecialchars($settings['tiktok_video_2']) ?>
                                    </a>
                                </div>
                                <button type="submit"
                                        name="delete_field"
                                        value="tiktok_video_2"
                                        class="btn btn-danger btn-sm ms-2"
                                        onclick="return confirm('Remove this TikTok video?')">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <input type="url" 
                               class="form-control" 
                               id="tiktok_video_2" 
                               name="tiktok_video_2" 
                               value="<?= htmlspecialchars($settings['tiktok_video_2'] ?? '') ?>"
                               placeholder="https://www.tiktok.com/@username/video/1234567890">
                        <div class="help-text">
                            <i class="bi bi-lightbulb me-1"></i>
                            Paste new URL to update, or clear to remove
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Instagram Section -->
                <div class="mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-instagram me-2"></i>Instagram Posts
                    </h5>

                    <div class="mb-3">
                        <label for="instagram_post_1" class="form-label fw-bold">
                            Instagram Post 1 URL
                            <?php if (!empty($settings['instagram_post_1'])): ?>
                                <span class="badge bg-success">Active</span>
                            <?php endif; ?>
                        </label>
                        
                        <?php if (!empty($settings['instagram_post_1'])): ?>
                        <div class="current-post">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block mb-1">Current:</small>
                                    <a href="<?= htmlspecialchars($settings['instagram_post_1']) ?>" target="_blank" class="text-break">
                                        <?= htmlspecialchars($settings['instagram_post_1']) ?>
                                    </a>
                                </div>
                                <button type="submit"
                                        name="delete_field"
                                        value="instagram_post_1"
                                        class="btn btn-danger btn-sm ms-2"
                                        onclick="return confirm('Remove this Instagram post?')">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <input type="url" 
                               class="form-control" 
                               id="instagram_post_1" 
                               name="instagram_post_1" 
                               value="<?= htmlspecialchars($settings['instagram_post_1'] ?? '') ?>"
                               placeholder="https://www.instagram.com/p/ABC123xyz/">
                        <div class="help-text">
                            <i class="bi bi-lightbulb me-1"></i>
                            Paste new URL to update, or clear to remove
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="instagram_post_2" class="form-label fw-bold">
                            Instagram Post 2 URL
                            <?php if (!empty($settings['instagram_post_2'])): ?>
                                <span class="badge bg-success">Active</span>
                            <?php endif; ?>
                        </label>
                        
                        <?php if (!empty($settings['instagram_post_2'])): ?>
                        <div class="current-post">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block mb-1">Current:</small>
                                    <a href="<?= htmlspecialchars($settings['instagram_post_2']) ?>" target="_blank" class="text-break">
                                        <?= htmlspecialchars($settings['instagram_post_2']) ?>
                                    </a>
                                </div>
                                <button type="submit"
                                        name="delete_field"
                                        value="instagram_post_2"
                                        class="btn btn-danger btn-sm ms-2"
                                        onclick="return confirm('Remove this Instagram post?')">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <input type="url" 
                               class="form-control" 
                               id="instagram_post_2" 
                               name="instagram_post_2" 
                               value="<?= htmlspecialchars($settings['instagram_post_2'] ?? '') ?>"
                               placeholder="https://www.instagram.com/p/ABC123xyz/">
                        <div class="help-text">
                            <i class="bi bi-lightbulb me-1"></i>
                            Paste new URL to update, or clear to remove
                        </div>
                    </div>
                </div>

                <!-- Facebook Section -->
                <div class="mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-facebook me-2"></i>Facebook Post
                    </h5>

                    <div class="mb-3">
                        <label for="facebook_post" class="form-label fw-bold">
                            Facebook Post URL
                            <?php if (!empty($settings['facebook_post'])): ?>
                                <span class="badge bg-success">Active</span>
                            <?php endif; ?>
                        </label>

                        <?php if (!empty($settings['facebook_post'])): ?>
                        <div class="current-post">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block mb-1">Current:</small>
                                    <a href="<?= htmlspecialchars($settings['facebook_post']) ?>" target="_blank" class="text-break">
                                        <?= htmlspecialchars($settings['facebook_post']) ?>
                                    </a>
                                </div>
                                <button type="submit"
                                        name="delete_field"
                                        value="facebook_post"
                                        class="btn btn-danger btn-sm ms-2"
                                        onclick="return confirm('Remove this Facebook post?')">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <input type="url"
                               class="form-control"
                               id="facebook_post"
                               name="facebook_post"
                               value="<?= htmlspecialchars($settings['facebook_post'] ?? '') ?>"
                               placeholder="https://www.facebook.com/{page}/posts/{id}">
                        <div class="help-text">
                            <i class="bi bi-lightbulb me-1"></i>
                            Paste a public post URL (or page post link) to embed, or clear to remove
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Instructions Box -->
                <div class="preview-box">
                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-lightbulb text-warning me-2"></i>How to get the URLs:
                    </h6>
                    <ul class="mb-0">
                        <li><strong>TikTok:</strong> Open your video → Click "Share" → Copy link</li>
                        <li><strong>Instagram:</strong> Open your post → Click "..." → "Copy link"</li>
                        <li><strong>Facebook:</strong> Open the post on Facebook → Click the three dots or Share → Copy link</li>
                        <li><strong>Update one post:</strong> Just change that field and click Save</li>
                        <li><strong>Remove a post:</strong> Click the red "Remove" button or clear the field</li>
                    </ul>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="<?= $is_super_admin ? '/ACE/admin/super_admin/dashboard.php' : '/ACE/admin/dashboard.php' ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($settings)): ?>
    <div class="card mt-4">
        <div class="card-body">
            <h6 class="fw-bold mb-2">Last Updated</h6>
            <p class="text-muted mb-0">
                <i class="bi bi-clock me-1"></i>
                <?= isset($settings['updated_at']) ? date('F j, Y g:i A', strtotime($settings['updated_at'])) : 'Never' ?>
            </p>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>