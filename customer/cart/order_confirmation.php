<?php
session_start();
include('../../includes/connection.php');

// Ensure the user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../../login.php');
    exit();
}

$order_id = $_GET['order_id'];

// Fetch the order details
$order_sql = "SELECT o.id, o.total_price, o.status, p.name, od.quantity, od.price
              FROM orders o
              JOIN order_details od ON o.id = od.order_id
              JOIN products p ON od.product_id = p.id
              WHERE o.id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

$order_details = [];
while ($row = $order_result->fetch_assoc()) {
    $order_details[] = $row;
}

$order_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>

</head>
<style>/* General Page Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #F0F8FF; /* Baby blue background */
    margin: 0;
    padding: 0;
}

.container {
    width: 80%;
    margin: 0 auto;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

h1, h2, h3 {
    color: #1E90FF; /* Baby blue */
    text-align: center;
}

/* Table Styles */
.cart-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.cart-table th,
.cart-table td {
    padding: 12px;
    text-align: left;
}

.cart-table th {
    background-color: #1E90FF;
    color: white;
    font-size: 16px;
}

.cart-table td {
    background-color: #f9f9f9;
    color: #333;
}

.cart-table img {
    max-width: 50px;
    border-radius: 5px;
}

/* Order Summary Section */
.order-summary {
    margin-top: 20px;
    padding: 20px;
    background-color: #f1faff;
    border: 1px solid #1E90FF;
    border-radius: 10px;
}

/* Buttons */
.confirm-order-button, .back-button {
    background-color: #1E90FF;
    color: white;
    padding: 15px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin: 10px 0;
}

.confirm-order-button:hover, .back-button:hover {
    background-color: #4682B4; /* Darker baby blue */
}

/* Back to Cart Section */
.back-to-cart {
    text-align: center;
    margin-top: 20px;
}

/* Delivery Information Section */
.delivery-info {
    background-color: #f0f8ff;
    padding: 15px;
    border: 1px solid #1E90FF;
    border-radius: 10px;
    margin-top: 20px;
    color: #333;
}

.delivery-info p {
    margin: 0;
    font-size: 14px;
}

.delivery-info span {
    font-weight: bold;
}

/* Buttons */
.confirm-order-button, .back-button {
    background-color: #1E90FF;
    color: white;
    padding: 15px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin: 10px 0;
}

.confirm-order-button:hover, .back-button:hover {
    background-color: #4682B4; /* Darker baby blue */
}

/* Back to Cart Section */
.back-to-cart {
    text-align: center;
    margin-top: 20px;
}


</style>
<body>

    <div class="container">
        <h1>Order Confirmation</h1>

        <h2>Order #<?php echo $order_id; ?></h2>
        <p>Status: <?php echo ucfirst($order_details[0]['status']); ?></p>

        <h3>Order Summary</h3>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_details as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Total Price: $<?php echo number_format($order_details[0]['total_price'], 2); ?></h3>

        <p>Your order is being processed. You will be notified once it is shipped.</p>

        <!-- Back to Cart Button -->
        <div class="back-to-cart">
            <a href="cart.php" class="back-button">Back to Cart</a>
        </div>


    </div>

</body>

</html>