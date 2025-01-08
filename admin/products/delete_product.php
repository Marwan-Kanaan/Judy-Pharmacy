<?php
session_start();
include '../../includes/connection.php';

// Check if the `id` parameter is passed
if (isset($_GET['id'])) {
    $productId = intval($_GET['id']); // Sanitize the input

    // Retrieve the product's image path before deletion
    $sqlGetImage = "SELECT image_path FROM products WHERE id = ?";
    $stmt = $conn->prepare($sqlGetImage);
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $imagePath = "../../" . $product['image_path'];

        // Delete the product from the database
        $sqlDelete = "DELETE FROM products WHERE id = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bind_param('i', $productId);

        if ($stmtDelete->execute()) {
            // If the product has an associated image, delete it from the server
            if (file_exists($imagePath) && is_file($imagePath)) {
                unlink($imagePath);
            }

            $_SESSION['success'] = "Product deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete product.";
        }

        $stmtDelete->close();
    } else {
        $_SESSION['error'] = "Product not found.";
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the products page
    header("Location: view_all_products.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: view_all_products.php");
    exit();
}
?>
