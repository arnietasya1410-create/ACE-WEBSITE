<?php
require_once __DIR__ . '/_inc.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ACE/admin/directcost.php');
    exit;
}

if (!ace_csrf_validate($_POST['csrf_token'] ?? '')) {
    http_response_code(400);
    exit('Invalid CSRF token');
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    flash_set('Database is not available.');
    header('Location: /ACE/admin/directcost.php');
    exit;
}

$calcName = trim((string)($_POST['calc_name'] ?? ''));
if ($calcName === '') {
    flash_set('Please provide a calculator name before saving.');
    header('Location: /ACE/admin/directcost.php');
    exit;
}

if (mb_strlen($calcName) > 150) {
    $calcName = mb_substr($calcName, 0, 150);
}

$summaryJson = (string)($_POST['summary_payload'] ?? '');
$detailsJson = (string)($_POST['calculation_payload'] ?? '');

$summary = json_decode($summaryJson, true);
$details = json_decode($detailsJson, true);
$recordId = isset($_POST['record_id']) ? (int)$_POST['record_id'] : 0;

if (!is_array($summary) || !is_array($details)) {
    flash_set('Unable to save: invalid calculation payload.');
    header('Location: /ACE/admin/directcost.php');
    exit;
}

$tableCheck = $conn->query("SHOW TABLES LIKE 'cost_calculator_records'");
if (!$tableCheck || $tableCheck->num_rows === 0) {
    flash_set('Table cost_calculator_records is missing. Run admin/sql/cost_calculator_tables.sql first.');
    header('Location: /ACE/admin/directcost.php');
    exit;
}

$adminId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;
$adminUser = (string)($_SESSION['admin_user'] ?? 'Admin');

$participants = (float)($summary['participants'] ?? 0);
$suggestedFee = (float)($summary['suggested_fee'] ?? 0);
$profitMargin = (float)($summary['profit_margin'] ?? 25);

$subtotalA = (float)($summary['subtotals']['A'] ?? 0);
$subtotalB = (float)($summary['subtotals']['B'] ?? 0);
$subtotalC = (float)($summary['subtotals']['C'] ?? 0);
$subtotalD = (float)($summary['subtotals']['D'] ?? 0);
$subtotalE = (float)($summary['subtotals']['E'] ?? 0);

$ete = (float)($summary['expected_total_expenses'] ?? 0);
$contingency = (float)($summary['contingency'] ?? 0);
$scete = (float)($summary['subtotal_after_contingency'] ?? 0);
$management = (float)($summary['management_service_charges'] ?? 0);
$smsc = (float)($summary['subtotal_after_service_charges'] ?? 0);
$profitAmount = (float)($summary['profit_amount'] ?? 0);
$spf = (float)($summary['subtotal_after_profit_margin'] ?? 0);
$hrd = (float)($summary['hrd_corp_charges'] ?? 0);
$totalAfterHrd = (float)($summary['subtotal_after_hrd_charges'] ?? 0);
$minFee = (float)($summary['minimum_fee_per_participant'] ?? 0);
$minPax = (int)($summary['minimum_participants_to_cover_cost'] ?? 0);

$isUpdate = $recordId > 0;

if ($isUpdate) {
    $existingStmt = $conn->prepare("SELECT id FROM cost_calculator_records WHERE id = ? LIMIT 1");
    if (!$existingStmt) {
        flash_set('Unable to update record (prepare failed).');
        header('Location: /ACE/admin/directcost.php');
        exit;
    }
    $existingStmt->bind_param('i', $recordId);
    $existingStmt->execute();
    $existingRes = $existingStmt->get_result();
    $exists = $existingRes && $existingRes->fetch_assoc();
    $existingStmt->close();

    if (!$exists) {
        flash_set('Record not found for update.');
        header('Location: /ACE/admin/directcost.php');
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE cost_calculator_records SET
            calc_name = ?,
            created_by_admin_id = ?,
            created_by_username = ?,
            participants = ?,
            suggested_fee = ?,
            profit_margin = ?,
            subtotal_a = ?,
            subtotal_b = ?,
            subtotal_c = ?,
            subtotal_d = ?,
            subtotal_e = ?,
            expected_total_expenses = ?,
            contingency = ?,
            subtotal_after_contingency = ?,
            management_service_charges = ?,
            subtotal_after_service_charges = ?,
            profit_amount = ?,
            subtotal_after_profit_margin = ?,
            hrd_corp_charges = ?,
            subtotal_after_hrd_charges = ?,
            minimum_fee_per_participant = ?,
            minimum_participants_to_cover_cost = ?,
            calculation_payload = ?,
            summary_payload = ?
        WHERE id = ?"
    );

    if (!$stmt) {
        flash_set('Unable to update record (prepare failed).');
        header('Location: /ACE/admin/directcost.php');
        exit;
    }

    $stmt->bind_param(
        'sisd' .
        'ddddd' .
        'ddddd' .
        'ddddd' .
        'ddissi',
        $calcName, $adminId, $adminUser,
        $participants, $suggestedFee, $profitMargin,
        $subtotalA, $subtotalB, $subtotalC, $subtotalD, $subtotalE,
        $ete, $contingency, $scete,
        $management, $smsc,
        $profitAmount, $spf,
        $hrd, $totalAfterHrd,
        $minFee, $minPax,
        $detailsJson, $summaryJson,
        $recordId
    );

    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        flash_set('Unable to update record.');
        header('Location: /ACE/admin/directcost.php?edit_id=' . $recordId);
        exit;
    }

    if (function_exists('log_cost_calculator_saved')) {
        if (function_exists('log_cost_calculator_updated')) {
            log_cost_calculator_updated($recordId, $calcName);
        } else {
            log_cost_calculator_saved($recordId, $calcName);
        }
    }
} else {
    $stmt = $conn->prepare(
        "INSERT INTO cost_calculator_records (
            calc_name, created_by_admin_id, created_by_username,
            participants, suggested_fee, profit_margin,
            subtotal_a, subtotal_b, subtotal_c, subtotal_d, subtotal_e,
            expected_total_expenses, contingency, subtotal_after_contingency,
            management_service_charges, subtotal_after_service_charges,
            profit_amount, subtotal_after_profit_margin,
            hrd_corp_charges, subtotal_after_hrd_charges,
            minimum_fee_per_participant, minimum_participants_to_cover_cost,
            calculation_payload, summary_payload
        ) VALUES (
            ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?,
            ?, ?,
            ?, ?,
            ?, ?,
            ?, ?
        )"
    );

    if (!$stmt) {
        flash_set('Unable to save record (prepare failed).');
        header('Location: /ACE/admin/directcost.php');
        exit;
    }

    $stmt->bind_param(
        'sisd' .
        'ddddd' .
        'ddddd' .
        'ddddd' .
        'ddiss',
        $calcName, $adminId, $adminUser,
        $participants, $suggestedFee, $profitMargin,
        $subtotalA, $subtotalB, $subtotalC, $subtotalD, $subtotalE,
        $ete, $contingency, $scete,
        $management, $smsc,
        $profitAmount, $spf,
        $hrd, $totalAfterHrd,
        $minFee, $minPax,
        $detailsJson, $summaryJson
    );

    $ok = $stmt->execute();
    $recordId = $ok ? (int)$stmt->insert_id : 0;
    $stmt->close();

    if (!$ok) {
        flash_set('Unable to save record.');
        header('Location: /ACE/admin/directcost.php');
        exit;
    }

    if (function_exists('log_cost_calculator_saved')) {
        log_cost_calculator_saved($recordId, $calcName);
    }
}

flash_set('Cost calculator record saved successfully.');
header('Location: /ACE/admin/cost_calculator_records.php');
exit;
