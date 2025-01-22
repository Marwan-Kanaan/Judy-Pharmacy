<?php
session_start();
include '../../includes/connection.php';

// Check if user_id session exists and role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin role required.');
}

$user_id = $_SESSION['user_id'];

// Get order ID
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch order details
$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Order not found!";
    exit();
}

$order = $result->fetch_assoc();

// Fetch ordered items
$sqlItems = "SELECT oi.*, p.name AS product_name, p.price AS product_price, p.stock AS product_stock 
             FROM order_details oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?";
$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param("i", $order_id);
$stmtItems->execute();
$itemsResult = $stmtItems->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start a transaction
    $conn->begin_transaction();

    try {
        // Handle form submission
        $status = $_POST['status'];

        // Initialize total price variable
        $new_total_price = 0;

        // Loop through ordered items and update quantities if necessary
        foreach ($_POST['quantities'] as $item_id => $new_quantity) {
            $new_quantity = intval($new_quantity);

            // Fetch the current quantity from the order_items table
            $sqlCurrentQuantity = "SELECT quantity FROM order_details WHERE order_id = ? AND product_id = ?";
            $stmtCurrentQuantity = $conn->prepare($sqlCurrentQuantity);
            $stmtCurrentQuantity->bind_param("ii", $order_id, $item_id);
            $stmtCurrentQuantity->execute();
            $currentQuantityResult = $stmtCurrentQuantity->get_result();
            $currentQuantity = $currentQuantityResult->fetch_assoc()['quantity'];

            // Calculate the difference in quantity
            $quantity_diff = $new_quantity - $currentQuantity;

            // Fetch the product's current stock and price
            $productSql = "SELECT stock, price FROM products WHERE id = ?";
            $productStmt = $conn->prepare($productSql);
            $productStmt->bind_param("i", $item_id);
            $productStmt->execute();
            $productResult = $productStmt->get_result();
            $product = $productResult->fetch_assoc();
            $current_stock = $product['stock'];
            $product_price = $product['price'];

            // Update stock based on quantity difference
            if ($quantity_diff > 0) {
                // If quantity is increased, decrease stock
                $updateStockSql = "UPDATE products SET stock = stock - ? WHERE id = ?";
                $updateStockStmt = $conn->prepare($updateStockSql);
                $updateStockStmt->bind_param("ii", abs($quantity_diff), $item_id);
                $updateStockStmt->execute();
            } elseif ($quantity_diff < 0) {
                // If quantity is decreased, increase stock
                $updateStockSql = "UPDATE products SET stock = stock + ? WHERE id = ?";
                $updateStockStmt = $conn->prepare($updateStockSql);
                $updateStockStmt->bind_param("ii", abs($quantity_diff), $item_id);
                $updateStockStmt->execute();
            }

            // Update the quantity in the order_items table
            $updateItemSql = "UPDATE order_details SET quantity = ? WHERE order_id = ? AND product_id = ?";
            $updateItemStmt = $conn->prepare($updateItemSql);
            $updateItemStmt->bind_param("iii", $new_quantity, $order_id, $item_id);
            $updateItemStmt->execute();

            // Calculate the total price for the updated quantity
            $new_total_price += $new_quantity * $product_price;
        }

        // If the order status is 'cancelled', add the quantities back to stock
        if ($status === 'cancelled') {
            foreach ($itemsResult as $item) {
                $product_id = $item['product_id'];
                $cancelled_quantity = $item['quantity'];

                // Add the cancelled quantities back to stock
                $updateStockSql = "UPDATE products SET stock = stock + ? WHERE id = ?";
                $updateStockStmt = $conn->prepare($updateStockSql);
                $updateStockStmt->bind_param("ii", $cancelled_quantity, $product_id);
                $updateStockStmt->execute();
            }
        }

        // Update the order status and total price
        $sqlUpdateOrder = "UPDATE orders SET status = ?, total_price = ? WHERE id = ?";
        $stmtUpdateOrder = $conn->prepare($sqlUpdateOrder);
        $stmtUpdateOrder->bind_param("sdi", $status, $new_total_price, $order_id);

        if ($stmtUpdateOrder->execute()) {
            // Commit the transaction if everything went well
            $conn->commit();
            header("Location: view_all_orders.php?message=Order+updated+successfully");
            exit();
        } else {
            // Rollback if there's an error in updating the order status
            $conn->rollback();
            echo "Error: " . $stmtUpdateOrder->error;
        }
    } catch (Exception $e) {
        // If any error occurs, rollback the transaction
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            background-color: #2c3e50;
            color: #ecf0f1;
            width: 198px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            margin-bottom: 25px;
            text-align: center;
            font-size: 1.5rem;
        }

        .sidebar a {
            text-decoration: none;
            color: #ecf0f1;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        .content {
            flex: 1;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #2980b9;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group img {
            max-width: 100px;
            margin-top: 10px;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .radio-group label {
            font-weight: normal;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <a href="../../index.php">Home</a>
            <a href="../../admin/dashboard.php">Dashboard</a>
            <a href="../users/view_all_users.php">Users</a>
            <a href="../products/view_all_products.php">Products</a>
            <a href="view_all_orders.php">Orders</a>
            <a href="../prescriptions/view_all_prescriptions.php">Prescriptions</a>
            <a href="../contacts/view_all_contacts.php">Contacts</a>
            <a href="../../includes/logout.php">Log out</a>
        </div>

        <!-- Content -->
        <div class="content">
            <h1>Edit Order</h1>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="status">Order Status:</label>
                    <select id="status" name="status" required>
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>completed</option>
                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <h3>Ordered Items:</h3>
                <?php while ($item = $itemsResult->fetch_assoc()) { ?>
                    <div class="form-group">
                        <label for="quantity_<?= $item['product_id'] ?>"><?= $item['product_name'] ?> (Current Stock: <?= $item['product_stock'] ?>):</label>
                        <input type="number" name="quantities[<?= $item['product_id'] ?>]" id="quantity_<?= $item['product_id'] ?>" value="<?= $item['quantity'] ?>" min="0" required>
                    </div>
                <?php } ?>

                <button type="submit">Update Order</button>
            </form>
        </div>
    </div>
</body>

</html>