<?php
session_start();
include('../includes/connection.php');

// Ensure the user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the users table
$userSql = "SELECT u.name, u.email, u.phone_number, a.street, a.city, a.state, a.country, u.image_path
            FROM users u
            JOIN addresses a ON u.id = a.user_id
            WHERE u.id = ?";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userResult = $stmt->get_result();

if ($userResult->num_rows > 0) {
    $userDetails = $userResult->fetch_assoc();
} else {
    echo "User not found!";
    exit();
}

// Fetch user orders with order details and quantities, grouped by order ID
$orderSql = "SELECT o.id, o.total_price, o.status, o.created_at, 
                    GROUP_CONCAT(p.name ORDER BY od.product_id) AS product_names, 
                    GROUP_CONCAT(od.quantity ORDER BY od.product_id) AS quantities
             FROM orders o
             JOIN order_details od ON o.id = od.order_id
             JOIN products p ON od.product_id = p.id
             WHERE o.customer_id = ?
             GROUP BY o.id";

$orderStmt = $conn->prepare($orderSql);
$orderStmt->bind_param("i", $user_id);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

// Fetch user prescriptions
$prescriptionSql = "SELECT p.id, p.product_id, p.created_at, p.status, p.image, pr.name AS product_name, ph.name AS pharmacist_name
                    FROM prescriptions p
                    JOIN products pr ON p.product_id = pr.id
                    JOIN users ph ON p.pharmacist_id = ph.id
                    WHERE p.customer_id = ?";

$prescriptionStmt = $conn->prepare($prescriptionSql);
$prescriptionStmt->bind_param("i", $user_id);
$prescriptionStmt->execute();
$prescriptionResult = $prescriptionStmt->get_result();

$stmt->close();
$orderStmt->close();
$prescriptionStmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Your existing styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }

        .profile-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
            text-align: center;
        }

        .profile-photo img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-card h2 {
            color: #333;
            margin-top: 15px;
        }

        .profile-card p {
            color: #777;
            margin-top: 5px;
        }

        .profile-card .user-details {
            text-align: left;
            margin-top: 20px;
        }

        .user-details table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .user-details table,
        .user-details th,
        .user-details td {
            border: 1px solid #ddd;
        }

        .user-details th,
        .user-details td {
            padding: 10px;
            text-align: left;
        }

        .user-details th {
            background-color: #5b9bd5;
            color: white;
        }

        .table-container {
            margin-top: 30px;
        }

        .table-container h3 {
            color: #333;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-container th,
        .table-container td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .table-container th {
            background-color: #5b9bd5;
            color: white;
        }

        button {
            padding: 12px;
            background-color: #5b9bd5;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            margin-top: 10px;
        }

        button:hover {
            background-color: #4a8fc4;
        }

        .back-to-home {
            text-align: center;
            display: block;
            margin-top: 20px;
            font-size: 1.1em;
            color: #5b9bd5;
            text-decoration: none;
        }

        .back-to-home:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-photo">
                <img src="<?php echo $userDetails['image_path'] ? $userDetails['image_path'] : 'default-avatar.jpg'; ?>" alt="Profile Photo">
            </div>
            <h2><?php echo $userDetails['name']; ?></h2>
            <p><?php echo $userDetails['email']; ?></p>
            <div class="user-details">
                <table>
                    <tr>
                        <th>Phone Number</th>
                        <td><?php echo $userDetails['phone_number']; ?></td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td><?php echo $userDetails['street'] . ', ' . $userDetails['city'] . ', ' . $userDetails['state'] . ', ' . $userDetails['country']; ?></td>
                    </tr>
                </table>
            </div>
            <!-- Update Details Button -->
            <a href="update_profile.php">
                <button>Update Details</button>
            </a>
            <!-- Orders Table -->
            <div class="table-container">
                <h3>Your Orders</h3>
                <table>
                    <tr>
                        <th>Order ID</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Order Details</th>
                    </tr>
                    <?php while ($order = $orderResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo '$' . number_format($order['total_price'], 2); ?></td>
                            <td><?php echo ucfirst($order['status']); ?></td>
                            <td><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></td>
                            <td>
                                <?php 
                                    $productNames = explode(',', $order['product_names']);
                                    $quantities = explode(',', $order['quantities']);
                                    $orderDetails = '';
                                    
                                    for ($i = 0; $i < count($productNames); $i++) {
                                        $orderDetails .= $productNames[$i] . ' (x' . $quantities[$i] . ')';
                                        if ($i < count($productNames) - 1) {
                                            $orderDetails .= ', ';
                                        }
                                    }

                                    echo $orderDetails;
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- Prescriptions Table -->
            <div class="table-container">
                <h3>Your Prescriptions</h3>
                <table>
                    <tr>
                        <th>Prescription ID</th>
                        <th>Medication</th>
                        <th>Product</th>
                        <th>Pharmacist</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Image</th>
                        <th>Download</th>
                    </tr>
                    <?php while ($prescription = $prescriptionResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $prescription['id']; ?></td>
                            <td><?php echo $prescription['medication']; ?></td>
                            <td><?php echo $prescription['product_name']; ?></td>
                            <td><?php echo $prescription['pharmacist_name']; ?></td>
                            <td><?php echo date('F j, Y', strtotime($prescription['created_at'])); ?></td>
                            <td><?php echo ucfirst($prescription['status']); ?></td>
                            <td>
                                <?php if (!empty($prescription['image_path'])): ?>
                                    <img src="<?php echo $prescription['image_path']; ?>" alt="Prescription Image" width="100" height="100">
                                <?php else: ?>
                                    No image available
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($prescription['image_path'])): ?>
                                    <a href="<?php echo $prescription['image_path']; ?>" download>
                                        <button>Download</button>
                                    </a>
                                <?php else: ?>
                                    No image to download
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <a href="../index.php" class="back-to-home">Back to Home</a>
        </div>
    </div>

</body>

</html>
