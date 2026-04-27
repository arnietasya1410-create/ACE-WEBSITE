<?php
// Simple mysqli connector used by pages in /User
// Edit these values to match your MySQL credentials
$DB_HOST = '127.0.0.1';
$DB_USER = 'ace3ip_user';
$DB_PASS = 'a8B3*cHi2hZgeeu~'; // set your MySQL password
$DB_NAME = 'ace_iiip';

$conn = null;

// Avoid fatal exceptions on connect errors; callers handle null $conn fallback.
mysqli_report(MYSQLI_REPORT_OFF);
try {
    $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($mysqli && !$mysqli->connect_errno) {
        $mysqli->set_charset('utf8mb4');
        $conn = $mysqli; // pages expect $conn as mysqli instance
    } else {
        error_log('khun.php: DB connection failed: ' . ($mysqli->connect_error ?? 'unknown'));
        $conn = null;
    }
} catch (Throwable $e) {
    // fail silently for now; pages will fallback to JSON if DB unavailable
    error_log('khun.php: DB connection exception: ' . $e->getMessage());
    $conn = null;
}
?>
