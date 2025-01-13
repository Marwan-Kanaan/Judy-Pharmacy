<?php
session_start();
include('../includes/connection.php');

// Ensure the user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items for the logged-in user
$sql = "SELECT c.id, p.name, p.price, c.quantity, p.image_path
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total price
$total_price = 0;
$cart_items = [];

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total_price += $row['price'] * $row['quantity'];
}

// Handle item removal from cart
if (isset($_GET['remove'])) {
    $cart_item_id = $_GET['remove'];
    $remove_sql = "DELETE FROM cart WHERE id = ? AND customer_id = ?";
    $remove_stmt = $conn->prepare($remove_sql);
    $remove_stmt->bind_param("ii", $cart_item_id, $user_id);
    $remove_stmt->execute();
    header("Location: cart.php");
    exit();
}

// Handle quantity update
if (isset($_POST['update_quantity'])) {
    $cart_item_id = $_POST['cart_item_id'];
    $new_quantity = $_POST['quantity'];

    // Update quantity in the cart
    $update_sql = "UPDATE cart SET quantity = ? WHERE id = ? AND customer_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("iii", $new_quantity, $cart_item_id, $user_id);
    $update_stmt->execute();
    header("Location: cart.php");
    exit();
}

// Close the statement
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
    /* General styles for the cart page */
    body {
        background-color: #e7f3fe;
        /* Light baby blue background */
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 80%;
        margin: 20px auto;
        background-color: #ffffff;
        /* White container background */
        padding: 20px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        /* Subtle shadow */
        border-radius: 10px;
        /* Rounded corners */
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
        color: #007acc;
        /* Baby blue text for the title */
        font-size: 28px;
    }

    /* Table and Cart Layout */
    .cart-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #f9fbff;
        /* Soft blue background for the table */
        border-radius: 8px;
        overflow: hidden;
    }

    .cart-table th,
    .cart-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #dbe4f0;
    }

    .cart-table th {
        background-color: #007acc;
        /* Baby blue header */
        color: white;
        font-weight: bold;
        text-transform: uppercase;
    }

    .cart-table td {
        vertical-align: middle;
    }

    .cart-table img {
        width: 50px;
        height: auto;
        border-radius: 5px;
    }

    /* Quantity Input Styling */
    .quantity-input {
        width: 60px;
        padding: 5px;
        text-align: center;
        font-size: 14px;
        border: 1px solid #dbe4f0;
        border-radius: 5px;
        background-color: #f1f9ff;
        /* Light blue input background */
        color: #007acc;
        /* Baby blue text */
    }

    /* Action Buttons (Remove and Update) */
    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .remove-button,
    .update-button {
        padding: 8px 16px;
        font-size: 14px;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        width: 90px;
        font-weight: bold;
        border: none;
        display: inline-block;
    }

    .remove-button {
        background-color: #ff6b6b;
        /* Soft red */
        color: white;
    }

    .remove-button:hover {
        background-color: #e63946;
        /* Darker red */
    }

    .update-button {
        background-color: #4dabf7;
        /* Baby blue */
        color: white;
    }

    .update-button:hover {
        background-color: #007acc;
        /* Darker blue */
    }

    /* Cart Total Section */
    .cart-total {
        margin-top: 20px;
        font-size: 18px;
        text-align: right;
        color: #007acc;
        /* Baby blue */
    }

    .checkout-button {
        background-color: #4dabf7;
        /* Baby blue button */
        padding: 10px 20px;
        text-decoration: none;
        color: white;
        font-weight: bold;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    /* Back to Products Button */
    .back-button {
        background-color: #d0ebff;
        /* Soft baby blue */
        color: #007acc;
        /* Stronger baby blue for text */
        padding: 10px 20px;
        text-decoration: none;
        font-weight: bold;
        border-radius: 5px;
        margin-right: 10px;
        transition: background-color 0.3s ease;
    }

    .back-button:hover {
        background-color: #4dabf7;
        /* Darker baby blue on hover */
        color: white;
    }

    .checkout-button:hover {
        background-color: #007acc;
        /* Darker blue on hover */
    }

    /* Empty Cart Message */
    .container p {
        text-align: center;
        font-size: 16px;
        color: #333;
    }

    .container p a {
        color: #007acc;
        /* Baby blue links */
        text-decoration: none;
    }

    .container p a:hover {
        text-decoration: underline;
    }
</style>

<body>

    <div class="container">
        <h1>Your Shopping Cart</h1>

        <?php if (count($cart_items) > 0): ?>
            <form action="cart.php" method="POST">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><img src="<?php echo $item['image_path']; ?>" alt="<?php echo $item['name']; ?>" width="50"> <?php echo $item['name']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="99" class="quantity-input">
                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
                                </td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td class="action-buttons">
                                    <a href="cart.php?remove=<?php echo $item['id']; ?>" class="remove-button">Remove</a>
                                    <button type="submit" name="update_quantity" class="update-button">Update</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>

            <div class="cart-total">
                <p><strong>Total Price: $<?php echo number_format($total_price, 2); ?></strong></p>
                <a href="../products.php" class="back-button">Back to Products</a>
                <a href="checkout.php" class="checkout-button">Proceed to Checkout</a>
            </div>

        <?php else: ?>
            <p>Your cart is empty. <a href="../products.php">Browse products</a> to add items to your cart.</p>
        <?php endif; ?>

    </div>

</body>

</html>