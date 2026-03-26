<?php
// Simple mysqli connector used by pages in /User
// Edit these values to match your MySQL credentials
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; // set your MySQL password
$DB_NAME = 'ace';

$conn = null;
$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli && !$mysqli->connect_errno) {
    $mysqli->set_charset('utf8mb4');
    $conn = $mysqli; // pages expect $conn as mysqli instance
} else {
    // fail silently for now; pages will fallback to JSON if DB unavailable
    error_log('khun.php: DB connection failed: ' . ($mysqli->connect_error ?? 'unknown'));
    $conn = null;
}
?>