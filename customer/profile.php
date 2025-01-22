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
$userSql = "SELECT name, email, phone_number, image_path FROM users WHERE id = ?";
$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows > 0) {
    $userDetails = $userResult->fetch_assoc();
} else {
    echo "User not found!";
    exit();
}

// Fetch address details from the addresses table
$addressSql = "SELECT street, city, state, country FROM addresses WHERE user_id = ?";
$addressStmt = $conn->prepare($addressSql);
$addressStmt->bind_param("i", $user_id);
$addressStmt->execute();
$addressResult = $addressStmt->get_result();

if ($addressResult->num_rows > 0) {
    $addressDetails = $addressResult->fetch_assoc();
} else {
    // If no address is found, set defaults or handle accordingly
    $addressDetails = [
        'street' => null,
        'city' => null,
        'state' => null,
        'country' => null,
    ];
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
$prescriptionSql = "SELECT p.id, p.created_at, p.status, p.image, p.customer_id_image, pr.name AS product_name, pr.price AS product_price, ph.name AS pharmacist_name
                    FROM prescriptions p
                    JOIN prescription_items pi ON p.id = pi.prescription_id
                    JOIN products pr ON pi.product_id = pr.id
                    LEFT JOIN users ph ON p.pharmacist_id = ph.id
                    WHERE p.customer_id = ?";
$prescriptionStmt = $conn->prepare($prescriptionSql);
$prescriptionStmt->bind_param("i", $user_id);
$prescriptionStmt->execute();
$prescriptionResult = $prescriptionStmt->get_result();

// Fetch contact messages for the user, including the updated_at timestamp
$messageSql = "SELECT customer_message, admin_message, updated_at, created_at FROM contact_message WHERE customer_id = ?";
$messageStmt = $conn->prepare($messageSql);
$messageStmt->bind_param("i", $user_id);
$messageStmt->execute();
$messageResult = $messageStmt->get_result();


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
            text-align: center;
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

        .download-button {
            padding: 10px 20px;
            background-color: #5b9bd5;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        /* Hover Effect */
        .download-button:hover {
            background-color: #4a8fc4;
        }

        .back-to-home {
            text-align: center;
            margin-top: 15px;
            display: block;
            padding: 8px 16px;
            background: #007acc;
            width: max-content;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-to-home:hover {
            text-decoration: underline;
        }

        /* Custom Scrollbar Style */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgb(140, 186, 211) #e0f7fa;
            /* Blue scrollbar with light blue track */
        }

        *::-webkit-scrollbar {
            width: 8px;
            /* Width of the scrollbar */
        }

        *::-webkit-scrollbar-track {
            background: rgb(255, 255, 255);
            /* Light blue track background */
        }

        *::-webkit-scrollbar-thumb {
            background-color: rgb(255, 255, 255);
            /* Blue scrollbar handle */
            border-radius: 4px;
            border: 2px solidrgb(255, 255, 255);
            /* Border for modern look */
        }

        *::-webkit-scrollbar-thumb:hover {
            background-color: rgb(255, 255, 255);
            /* Darker blue on hover */
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            .profile-card {
                padding: 15px;
            }

            .profile-photo img {
                width: 120px;
                height: 120px;
            }

            .user-details table,
            .table-container table {
                font-size: 14px;
            }

            .user-details th,
            .user-details td,
            .table-container th,
            .table-container td {
                padding: 8px;
            }

            button {
                font-size: 1em;
                padding: 10px;
            }

            .download-button {
                font-size: 14px;
                padding: 8px 16px;
            }
        }

        @media (max-width: 480px) {
            .profile-photo img {
                width: 100px;
                height: 100px;
            }

            h2 {
                font-size: 1.5em;
            }

            .user-details table,
            .table-container table {
                font-size: 12px;
            }

            .user-details th,
            .user-details td,
            .table-container th,
            .table-container td {
                padding: 5px;
            }

            button {
                font-size: 0.9em;
                padding: 8px;
            }

            .back-to-home {
                font-size: 0.9em;
                padding: 6px 12px;
            }

            .download-button {
                font-size: 12px;
                padding: 6px 12px;
            }
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
                        <td><?php echo $addressDetails['street'] . ', ' . $addressDetails['city'] . ', ' . $addressDetails['state'] . ', ' . $addressDetails['country']; ?></td>
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
                        <th>Cancellation</th>
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
                            <td>
                                <?php
                                $created_at = strtotime($order['created_at']);
                                $current_time = time();
                                $remaining_time = $created_at - $current_time;

                                if ($remaining_time > 0): ?>
                                    <input type="checkbox"
                                        name="order_action[]"
                                        value="<?php echo $order['id']; ?>"
                                        data-status="<?php echo $order['status']; ?>"
                                        <?php echo $order['status'] === 'pending' ? 'checked' : ''; ?>>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>



            <!-- Prescriptions Table -->
            <div class="table-container">
                <h3>Your Prescriptions</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Prescription ID</th>
                            <th>Products</th>
                            <th>Pharmacist</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Download prescription</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Initialize an array to keep track of the prescription IDs we've already processed
                        $processed_prescriptions = [];

                        // Loop through the fetched prescriptions
                        while ($prescription = $prescriptionResult->fetch_assoc()) {
                            $prescription_id = $prescription['id'];

                            // Check if the prescription has already been processed (to avoid duplicate rows)
                            if (!in_array($prescription_id, $processed_prescriptions)) {
                                // Display the prescription row
                                echo '<tr>';
                                echo '<td>' . $prescription['id'] . '</td>';

                                // Fetch and display all products associated with the prescription
                                echo '<td>';
                                $products_sql = "
                        SELECT pr.name AS product_name
                        FROM prescription_items pi
                        JOIN products pr ON pi.product_id = pr.id
                        WHERE pi.prescription_id = ?
                    ";
                                $product_stmt = $conn->prepare($products_sql);
                                $product_stmt->bind_param("i", $prescription_id);
                                $product_stmt->execute();
                                $product_result = $product_stmt->get_result();

                                // Display products for this prescription
                                $product_names = [];
                                while ($product = $product_result->fetch_assoc()) {
                                    $product_names[] = $product['product_name'];
                                }
                                echo implode(', ', $product_names);  // Display products in the same row
                                echo '</td>';

                                echo '<td>' . $prescription['pharmacist_name'] . '</td>';
                                echo '<td>' . date('F j, Y', strtotime($prescription['created_at'])) . '</td>';
                                echo '<td>' . ucfirst($prescription['status']) . '</td>';
                                echo '<td>';
                                if (!empty($prescription['image'])) {
                                    // Download link should also be relative to the joudi_pharmacy directory
                                    $download_path = "../images/users_uploads/user_prescriptions/" . $prescription['image'];
                                    echo '<a href="' . $download_path . '" download><button class="download-button">Download</button></a>';
                                } else {
                                    echo 'No image to download';
                                }
                                echo '</td>';
                                echo '</tr>';

                                // Mark this prescription as processed
                                $processed_prescriptions[] = $prescription_id;
                            }
                        }
                        ?>

                    </tbody>
                </table>

                <!-- Contact Messages Section -->
                <div class="table-container">
                    <h3>Your Contact Messages</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Message</th>
                                <th>Message Date</th>
                                <th>Reply Message</th>
                                <th>Reply Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($message = $messageResult->fetch_assoc()): ?>
                                <tr>
                                    <td data-label="Message"><?php echo htmlspecialchars($message['customer_message']); ?></td>
                                    <td data-label="Date Sent"><?php echo date('F j, Y, g:i a', strtotime($message['created_at'])); ?></td>
                                    <td data-label="Reply Message">
                                        <?php
                                        if (!empty($message['admin_message'])) {
                                            echo htmlspecialchars($message['admin_message']);
                                        } else {
                                            echo 'No reply yet';
                                        }

                                        ?>
                                    </td>
                                    <td data-label="Reply Date">
                                        <?php
                                        // Check if admin_message is empty before displaying updated_at
                                        if (!empty($message['admin_message']) && !empty($message['updated_at']) && $message['updated_at'] != '0000-00-00 00:00:00') {
                                            echo date('F j, Y, g:i a', strtotime($message['updated_at']));
                                        } else {
                                            echo 'No date yet'; // Leave empty if no reply yet
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>

                    </table>
                </div>

            </div>
            <a href="../index.php" class="back-to-home">Back to Home</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Select all checkboxes with the time-remaining data attribute
            const checkboxes = document.querySelectorAll('input[type="checkbox"][data-time-remaining]');

            checkboxes.forEach((checkbox) => {
                const remainingTime = parseInt(checkbox.dataset.timeRemaining, 10); // Get the remaining time

                console.log(`Checkbox ID: ${checkbox.value}, Remaining Time: ${remainingTime}s`);

                if (remainingTime > 0) {
                    // Set a timeout to hide the checkbox when the remaining time runs out
                    setTimeout(() => {
                        console.log(`Hiding checkbox ID: ${checkbox.value}`);
                        checkbox.closest("td").innerHTML = "N/A"; // Replace the checkbox with "N/A"
                    }, remainingTime * 1000);
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Function to toggle order status
            const toggleOrderStatus = (checkbox) => {
                const orderId = checkbox.value;
                const currentStatus = checkbox.dataset.status;
                const newStatus = currentStatus === "pending" ? "cancelled" : "pending";

                // Update the status on the server
                fetch("update_order_status.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: `order_id=${orderId}&status=${newStatus}`,
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            // Update the checkbox status
                            checkbox.dataset.status = newStatus;
                            checkbox.checked = newStatus === "pending";
                            alert(data.message);
                        } else {
                            alert("Error: " + data.message);
                            // Revert the checkbox state on failure
                            checkbox.checked = currentStatus === "pending";
                        }
                    })
                    .catch((error) => {
                        console.error("Error updating order status:", error);
                        alert("An error occurred while updating the status.");
                        // Revert the checkbox state on error
                        checkbox.checked = currentStatus === "pending";
                    });
            };

            // Attach the toggle event to all order checkboxes
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name="order_action[]"]');
            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener("change", () => toggleOrderStatus(checkbox));
            });
        });
    </script>



</body>

</html>