<?php
session_start();
include '../../includes/connection.php';

// Check if user_id session exists and role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin role required.');
}

// Get the prescription ID from the URL
$prescriptionId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($prescriptionId <= 0) {
    header("Location: view_all_prescriptions.php");
    exit();
}

// Start transaction to ensure data consistency
$conn->begin_transaction();

try {
    // Delete from prescription_items first to maintain foreign key integrity
    $sqlDeleteItems = "DELETE FROM prescription_items WHERE prescription_id = ?";
    $stmtDeleteItems = $conn->prepare($sqlDeleteItems);
    $stmtDeleteItems->bind_param("i", $prescriptionId);
    $stmtDeleteItems->execute();

    // Delete from prescriptions
    $sqlDeletePrescription = "DELETE FROM prescriptions WHERE id = ?";
    $stmtDeletePrescription = $conn->prepare($sqlDeletePrescription);
    $stmtDeletePrescription->bind_param("i", $prescriptionId);
    $stmtDeletePrescription->execute();

    // Commit transaction
    $conn->commit();

    // Redirect back with success message
    $_SESSION['message'] = "Prescription deleted successfully.";
    header("Location: view_all_prescriptions.php");
    exit();
} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();
    $_SESSION['error'] = "Failed to delete prescription. Please try again.";
    header("Location: view_all_prescriptions.php");
    exit();
}
?>
