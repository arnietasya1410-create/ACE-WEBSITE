<?php
// AES encryption key (auto-generated once)
$key = "accessaregranted";

// The REAL password in plain text
$realPassword = "oQfM31C3rTozjAi7"; 

// Encrypt it once
$encryptedPassword = openssl_encrypt($realPassword, "AES-256-CBC", $key, 0, substr($key, 0, 16));
?>