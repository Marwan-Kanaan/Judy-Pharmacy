<?php
session_start();
include '../../includes/connection.php';

// Check if user_id session exists and role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin role required.');
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $userId = $_GET['id'];

    // First, delete associated addresses
    $sqlDeleteAddress = "DELETE FROM addresses WHERE user_id = ?";
    $stmtDeleteAddress = $conn->prepare($sqlDeleteAddress);
    $stmtDeleteAddress->bind_param("i", $userId);
    $stmtDeleteAddress->execute();

    if ($stmtDeleteAddress->affected_rows > 0) {
        // Now, delete the user
        $sqlDeleteUser = "DELETE FROM users WHERE id = ?";
        $stmtDeleteUser = $conn->prepare($sqlDeleteUser);
        $stmtDeleteUser->bind_param("i", $userId);
        $stmtDeleteUser->execute();

        if ($stmtDeleteUser->affected_rows > 0) {
            // Redirect back to the view page with a success message
            header("Location: view_all_users.php?message=User deleted successfully");
            exit();
        } else {
            // Redirect back with an error message
            header("Location: view_all_users.php?message=Failed to delete user");
            exit();
        }
    } else {
        // Redirect back with an error message
        header("Location: view_all_users.php?message=Failed to delete associated addresses");
        exit();
    }
} else {
    // Redirect back with an error message for invalid request
    header("Location: view_all_users.php?message=Invalid request method");
    exit();
}

// Close connection
$conn->close();
