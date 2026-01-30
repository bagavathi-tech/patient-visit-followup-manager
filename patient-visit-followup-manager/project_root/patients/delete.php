<?php
require_once '../config/db.php';
checkLogin();

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid patient ID");
}

$patient_id = (int) $_GET['id'];

// OPTIONAL SAFETY: delete visits first (FK issue avoid)
$conn->query("DELETE FROM visits WHERE patient_id = $patient_id");

// Delete patient
$sql = "DELETE FROM patients WHERE patient_id = $patient_id";

if ($conn->query($sql)) {
    header("Location: list.php?success=deleted");
    exit;
} else {
    die("Delete failed: " . $conn->error);
}