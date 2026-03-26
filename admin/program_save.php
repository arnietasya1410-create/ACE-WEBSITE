<?php
require_once __DIR__ . '/_inc.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ACE/admin/program_list.php');
    exit;
}

if (!ace_csrf_validate($_POST['csrf_token'] ?? '')) {
    flash_set('Invalid request. Please try again.');
    header('Location: /ACE/admin/program_edit.php' . (!empty($_POST['programme_id']) ? '?id=' . (int)$_POST['programme_id'] : ''));
    exit;
}
$admin_username = $_SESSION['admin_user'] ?? 'Admin';
$programme_id = isset($_POST['programme_id']) ? (int)$_POST['programme_id'] : 0;

// Basic fields
$title       = trim($_POST['title'] ?? '');
$programme_category = trim($_POST['programme_category'] ?? '');
$description = trim($_POST['description'] ?? '');
$start_date  = $_POST['start_date'] ?? null;
$end_date    = $_POST['end_date'] ?? null;
$location    = trim($_POST['location'] ?? '');
$price       = isset($_POST['price']) ? (float)$_POST['price'] : 0;
$has_packages = isset($_POST['has_packages']) ? 1 : 0;
$is_active   = isset($_POST['is_active']) ? 1 : 0;
$form_url = trim($_POST['form_url'] ?? '');
if ($form_url === '') $form_url = null;
$person_in_charge = trim($_POST['person_in_charge'] ?? '');
if ($person_in_charge === '') $person_in_charge = null;

if (!ace_validate_url_or_empty($form_url)) {
    flash_set('Registration URL is invalid.');
    header('Location: /ACE/admin/program_edit.php' . ($programme_id ? "?id=$programme_id" : ''));
    exit;
}

// Payment methods (array of IDs)
$payment_methods = $_POST['payment_methods'] ?? [];
$payment_messages = $_POST['payment_message'] ?? [];

// Validate required fields
if ($title === '' || $programme_category === '' || ($has_packages == 0 && $price == 0)) {
    flash_set('Title, category, and price are required (unless this programme uses payment packages).');
    header('Location: /ACE/admin/program_edit.php' . ($programme_id ? "?id=$programme_id" : ''));
    exit;
}

// UPDATE or INSERT programme
$is_new = ($programme_id === 0);

if ($programme_id > 0) {
    // UPDATE existing
    $stmt = $conn->prepare("
        UPDATE programmes 
        SET title=?, programme_category=?, description=?, start_date=?, end_date=?, location=?, price=?, has_packages=?, is_active=?, form_url=?, person_in_charge=?, updated_at=NOW()
        WHERE programme_id=?
    ");
    if (!$stmt) {
        flash_set('Database error: ' . $conn->error);
        header('Location: /ACE/admin/program_edit.php?id=' . $programme_id);
        exit;
    }
    $stmt->bind_param(
        "ssssssdiissi",
        $title,
        $programme_category,
        $description,
        $start_date,
        $end_date,
        $location,
        $price,
        $has_packages,
        $is_active,
        $form_url,
        $person_in_charge,
        $programme_id
    );
    $stmt->execute();
    $stmt->close();
} else {
    // INSERT new
    $stmt = $conn->prepare("
        INSERT INTO programmes (title, programme_category, description, start_date, end_date, location, price, has_packages, is_active, form_url, person_in_charge, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    if (!$stmt) {
        flash_set('Database error: ' . $conn->error);
        header('Location: /ACE/admin/program_edit.php');
        exit;
    }
    $stmt->bind_param(
        "ssssssdiiss",
        $title,
        $programme_category,
        $description,
        $start_date,
        $end_date,
        $location,
        $price,
        $has_packages,
        $is_active,
        $form_url,
        $person_in_charge
    );
    $stmt->execute();
    $programme_id = $conn->insert_id;
    $stmt->close();
}

// ---------------------------------------
// Update programme_payment_methods table with messages
// Delete existing entries first
$conn->query("DELETE FROM programme_payment_methods WHERE programme_id = {$programme_id}");

// Insert selected payment methods with their custom messages
if (!empty($payment_methods)) {
    $stmt = $conn->prepare("INSERT INTO programme_payment_methods (programme_id, payment_method_id, message) VALUES (?, ?, ?)");
    
    if ($stmt) {
        foreach ($payment_methods as $pm_id) {
            $pm_id = (int)$pm_id;
            // Get the message for this payment method (if any)
            $msg = isset($payment_messages[$pm_id]) ? trim($payment_messages[$pm_id]) : null;
            if ($msg === '') $msg = null;
            
            $stmt->bind_param("iis", $programme_id, $pm_id, $msg);
            $stmt->execute();
        }
        $stmt->close();
    } else {
        flash_set('Failed to save payment methods: ' . $conn->error);
    }
}

// ---------------------------------------
// Handle image uploads
$upload_dir = __DIR__ . '/../uploads/programmes/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Check if files were uploaded
if (isset($_FILES['programme_images']) && !empty($_FILES['programme_images']['name'][0])) {
    $stmt = $conn->prepare("INSERT INTO programme_images (programme_id, filename, created_at) VALUES (?, ?, NOW())");
    $saved_any = false;

    if ($stmt) {
        foreach ($_FILES['programme_images']['name'] as $index => $original_name) {
            $tmp_name = $_FILES['programme_images']['tmp_name'][$index];
            $error = $_FILES['programme_images']['error'][$index];

            // Skip if error
            if ($error !== UPLOAD_ERR_OK) {
                continue;
            }

            $file_meta = [
                'tmp_name' => $tmp_name,
                'name' => $original_name,
                'error' => $error,
                'size' => $_FILES['programme_images']['size'][$index]
            ];
            if (!ace_validate_image_upload($file_meta)) {
                continue;
            }

            // Generate unique filename
            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $new_filename = 'programme_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest_path = $upload_dir . $new_filename;

            // Move uploaded file
            if (move_uploaded_file($tmp_name, $dest_path)) {
                // Store relative path for web access
                $web_path = '/ACE/uploads/programmes/' . $new_filename;
                $stmt->bind_param("is", $programme_id, $web_path);
                $stmt->execute();
                $saved_any = true;
            }
        }
        $stmt->close();
        if (!$saved_any) {
            flash_set('No images were saved. Please ensure files are valid images under 5MB.');
        }
    }
}

// LOG THE ACTIVITY (moved to the end, after everything is saved)
if ($is_new) {
    log_programme_created($programme_id, $title);
    flash_set('Programme created successfully!', 'success');
} else {
    log_programme_updated($programme_id, $title);
    flash_set('Programme updated successfully!', 'success');
}

// Redirect back to edit page (MOVED TO THE VERY END)
header("Location: /ACE/admin/program_edit.php?id=" . $programme_id);
exit;
?>