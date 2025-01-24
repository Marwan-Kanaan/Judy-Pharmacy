<?php
session_start();
include('../../includes/connection.php');

// Ensure the user is logged in and has the 'customer' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details (including phone number)
$user_sql = "SELECT name, email, phone_number FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_details = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Fetch address details from the addresses table
$address_sql = "SELECT street, city, state, country FROM addresses WHERE user_id = ?";
$address_stmt = $conn->prepare($address_sql);
$address_stmt->bind_param("i", $user_id);
$address_stmt->execute();
$address_details = $address_stmt->get_result()->fetch_assoc();
$address_stmt->close();

// Fetch cart items
$sql = "SELECT c.product_id, p.name, p.price, c.quantity
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_price = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total_price += $row['price'] * $row['quantity'];
}
$stmt->close();

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    // Insert into orders table
    $order_sql = "INSERT INTO orders (customer_id, total_price) VALUES (?, ?)";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("id", $user_id, $total_price);
    $order_stmt->execute();
    $order_id = $order_stmt->insert_id;
    $order_stmt->close();

    // Insert into order_details table and update stock in products table
    foreach ($cart_items as $item) {
        // Insert into order_details table
        $order_details_sql = "INSERT INTO order_details (order_id, product_id, quantity, price)
                              VALUES (?, ?, ?, ?)";
        $details_stmt = $conn->prepare($order_details_sql);
        $details_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $details_stmt->execute();
        $details_stmt->close();

        // Update the product stock in the products table
        $update_stock_sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
        $stock_stmt = $conn->prepare($update_stock_sql);
        $stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $stock_stmt->execute();
        $stock_stmt->close();
    }

    // Clear the cart
    $clear_cart_sql = "DELETE FROM cart WHERE customer_id = ?";
    $clear_cart_stmt = $conn->prepare($clear_cart_sql);
    $clear_cart_stmt->bind_param("i", $user_id);
    $clear_cart_stmt->execute();
    $clear_cart_stmt->close();

    // Redirect to order confirmation page
    header("Location: order_confirmation.php?order_id=$order_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
</head>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f0f8ff;
        color: #333;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 800px;
        margin: 20px auto;
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }

    h1,
    h2 {
        color: #007acc;
        margin-bottom: 20px;
    }

    p {
        margin: 10px 0;
    }

    .cart-table,
    .cart-table th,
    .cart-table td {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        border: 1px solid #ddd;
    }

    .cart-table th,
    .cart-table td {
        padding: 10px;
    }

    .cart-total {
        text-align: right;
        font-size: 1.2em;
        margin-top: 20px;
    }

    .delivery-info {
        background: #e7f5ff;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
    }

    .checkout-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }

    .back-to-cart-button,
    .confirm-order-button,
    .update-button {
        background: #007acc;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        text-align: center;
        cursor: pointer;
    }

    .back-to-cart-button:hover,
    .confirm-order-button:hover,
    .update-button:hover {
        background: #005a99;
    }
</style>

<body>
    <div class="container">
        <h1>Checkout</h1>

        <div class="user-details">
            <h2>Customer Details</h2>
            <form method="POST" action="update_user_details.php">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user_details['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_details['email']); ?></p>
                <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($user_details['phone_number']); ?></p>

                <h3>Address</h3>
                <p><strong>Street:</strong> <?php echo htmlspecialchars($address_details['street']); ?></p>
                <p><strong>City:</strong> <?php echo htmlspecialchars($address_details['city']); ?></p>
                <p><strong>State:</strong> <?php echo htmlspecialchars($address_details['state']); ?></p>
                <p><strong>Country:</strong> <?php echo htmlspecialchars($address_details['country']); ?></p>

                <button type="submit" class="update-button">Update Address</button>
            </form>
        </div>


        <div class="cart-summary">
            <h2>Order Summary</h2>
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
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-total">
                <p><strong>Total Price: $<?php echo number_format($total_price, 2); ?></strong></p>
            </div>
        </div>

        <div class="delivery-info">
            <h2>Delivery Information</h2>
            <p>🛒 <strong>Refund Policy:</strong> No refunds available once the order is placed.</p>
            <p>⏳ <strong>Cancellation Policy:</strong> Orders can be canceled within 1 hour.</p>
            <p>🚚 <strong>Delivery Time:</strong> Delivery takes between 1 hour to 3 days depending on your location.</p>
        </div>

        <div class="checkout-actions">
            <a href="cart.php" class="back-to-cart-button">Back to Cart</a>
            <form method="POST">
                <button type="submit" name="confirm_order" class="confirm-order-button">Confirm Order</button>
            </form>
        </div>
    </div>
</body>

</html>
