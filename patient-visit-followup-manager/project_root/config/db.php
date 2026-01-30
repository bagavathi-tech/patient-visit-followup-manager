<?php
// config/db.php - Add this after the connection code

session_start();
require_once __DIR__ . '/../includes/validation.php';
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'healthcare_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// ✅ ADD THIS FUNCTION:
/**
 * Escape string for security
 */
function escape($str) {
    global $conn;
    return $conn->real_escape_string($str);
}
// 🔐 Login check function
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /patient-visit-followup-manager/project_root/login.php");
        exit();
    }
}
// Rest of your functions...

?>