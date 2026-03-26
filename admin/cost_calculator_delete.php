<?php
require_once __DIR__ . '/_inc.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ACE/admin/cost_calculator_records.php');
    exit;
}

if (!ace_csrf_validate($_POST['csrf_token'] ?? '')) {
    http_response_code(400);
    exit('Invalid CSRF token');
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    flash_set('Database is not available.');
    header('Location: /ACE/admin/cost_calculator_records.php');
    exit;
}

$recordId = isset($_POST['record_id']) ? (int)$_POST['record_id'] : 0;
if ($recordId <= 0) {
    flash_set('Invalid record selected for deletion.');
    header('Location: /ACE/admin/cost_calculator_records.php');
    exit;
}

$tableCheck = $conn->query("SHOW TABLES LIKE 'cost_calculator_records'");
if (!$tableCheck || $tableCheck->num_rows === 0) {
    flash_set('Table cost_calculator_records is missing.');
    header('Location: /ACE/admin/cost_calculator_records.php');
    exit;
}

$stmt = $conn->prepare("SELECT id, calc_name, created_by_admin_id, created_by_username FROM cost_calculator_records WHERE id = ? LIMIT 1");
if (!$stmt) {
    flash_set('Unable to delete record (prepare failed).');
    header('Location: /ACE/admin/cost_calculator_records.php');
    exit;
}

$stmt->bind_param('i', $recordId);
$stmt->execute();
$res = $stmt->get_result();
$record = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$record) {
    flash_set('Record not found.');
    header('Location: /ACE/admin/cost_calculator_records.php');
    exit;
}

$currentAdminId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;
$currentAdminUser = (string)($_SESSION['admin_user'] ?? '');
$creatorAdminId = (int)($record['created_by_admin_id'] ?? 0);
$creatorAdminUser = (string)($record['created_by_username'] ?? '');

$allowedById = ($creatorAdminId > 0 && $currentAdminId > 0 && $creatorAdminId === $currentAdminId);
$allowedByUsername = ($creatorAdminId <= 0 && $creatorAdminUser !== '' && strcasecmp($creatorAdminUser, $currentAdminUser) === 0);

if (!$allowedById && !$allowedByUsername) {
    flash_set('Only the admin who created this calculator can delete it.');
    header('Location: /ACE/admin/cost_calculator_records.php');
    exit;
}

$deleteStmt = $conn->prepare("DELETE FROM cost_calculator_records WHERE id = ? LIMIT 1");
if (!$deleteStmt) {
    flash_set('Unable to delete record (prepare failed).');
    header('Location: /ACE/admin/cost_calculator_records.php');
    exit;
}

$deleteStmt->bind_param('i', $recordId);
$ok = $deleteStmt->execute();
$deleteStmt->close();

if (!$ok) {
    flash_set('Unable to delete record.');
    header('Location: /ACE/admin/cost_calculator_records.php');
    exit;
}

if (function_exists('log_cost_calculator_deleted')) {
    log_cost_calculator_deleted($recordId, (string)$record['calc_name']);
}

flash_set('Cost calculator record deleted successfully.');
header('Location: /ACE/admin/cost_calculator_records.php');
exit;
