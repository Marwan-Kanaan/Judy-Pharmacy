<?php
session_start();
include '../../includes/connection.php';

// Check if user_id session exists and role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin role required.');
}

$messageId = $_GET['id']; // Get the message ID from the URL

// Fetch message data
$sqlMessage = "SELECT cm.*, u.name AS customer_name, u.email AS customer_email, u.phone_number AS customer_phone 
               FROM contact_message cm
               INNER JOIN users u ON cm.customer_id = u.id 
               WHERE cm.id = ?";
$stmtMessage = $conn->prepare($sqlMessage);
$stmtMessage->bind_param("i", $messageId);
$stmtMessage->execute();
$resultMessage = $stmtMessage->get_result();
$message = $resultMessage->fetch_assoc();

if (!$message) {
    header("Location: view_all_contacts.php");
    exit();
}

// Handle form submission for the reply
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $adminMessage = $conn->real_escape_string(trim($_POST['admin_message']));

    // Replace newlines with a space (optional if you don't want to store line breaks)
    $adminMessage = str_replace(["\r", "\n"], "<br> ", $adminMessage);

    // Update admin message in the database
    $sqlUpdateMessage = "UPDATE contact_message SET admin_message = ? WHERE id = ?";
    $stmtUpdateMessage = $conn->prepare($sqlUpdateMessage);
    $stmtUpdateMessage->bind_param("si", $adminMessage, $messageId);
    $stmtUpdateMessage->execute();

    header("Location: view_all_contacts.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Contact Message</title>
    <style>
        /* Add styles similar to the previous example */
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
    </style>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <a href="../../index.php">Home</a>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../users/view_all_users.php">Users</a>
            <a href="../products/view_all_products.php">Products</a>
            <a href="../orders/view_all_orders.php">Orders</a>
            <a href="view_all_contacts.php">Contacts</a>
            <a href="../../includes/logout.php">Log out</a>
        </div>
        <div class="content">
            <h1>Reply to Contact Message</h1>

            <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($message['customer_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($message['customer_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($message['customer_phone']); ?></p>
            <p><strong>Customer Message:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($message['customer_message'])); ?></p>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="admin_message">Your Reply:</label>
                    <textarea id="admin_message" name="admin_message" rows="4" required><?php echo htmlspecialchars($message['admin_message']); ?></textarea>
                </div>

                <button type="submit" name="submit">Submit Reply</button>
            </form>
        </div>
    </div>
</body>

</html>
