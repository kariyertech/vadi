<?php
// Database connection parameters
$db_host = "db";
$db_port = "5432";
$db_name = "vadidb";
$db_user = "vadiadmin";
$db_password = "vadi2025!qazwsx";

// Function to hash a password
function generate_password_hash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to verify a password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
} 