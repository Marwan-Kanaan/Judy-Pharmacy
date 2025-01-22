<?php
include('../includes/connection.php');
session_start();

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    // Ensure the user is logged in and is a customer
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Update the order status in the database
    $sql = "UPDATE orders SET status = ? WHERE id = ? AND customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $new_status, $order_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
