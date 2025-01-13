<?php
session_start();
include('../includes/connection.php');

// Ensure the user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'];  // Get product ID from URL

// Check if product exists in the database
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();

if ($product) {
    // Check if the product is already in the cart
    $cart_sql = "SELECT * FROM cart WHERE customer_id = ? AND product_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("ii", $user_id, $product_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();

    if ($cart_result->num_rows > 0) {
        // If product is already in the cart, increase quantity
        $update_sql = "UPDATE cart SET quantity = quantity + 1 WHERE customer_id = ? AND product_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $user_id, $product_id);
        $update_stmt->execute();
    } else {
        // Add new product to cart
        $insert_sql = "INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, 1)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $user_id, $product_id);
        $insert_stmt->execute();
    }

    header('Location: cart.php');  // Redirect to cart page after adding
    exit();
} else {
    echo "Product not found!";
}
?>
