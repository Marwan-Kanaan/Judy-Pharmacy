<?php
session_start();
include '../../includes/connection.php';

// Check if user_id session exists and role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin role required.');
}

$user_id = $_SESSION['user_id'];

$userId = $_GET['id']; // Get the user ID from the URL

// Fetch user data
$sqlUser = "SELECT users.*, addresses.* FROM users
            INNER JOIN addresses ON users.id = addresses.user_id
            WHERE users.id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();

if (!$user) {
    header("Location: view_all_users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);

    // Address data
    $street = $conn->real_escape_string($_POST['street']);
    $city = $conn->real_escape_string($_POST['city']);
    $state = $conn->real_escape_string($_POST['state']);
    $country = $conn->real_escape_string($_POST['country']);

    // Start transaction to ensure data consistency
    $conn->begin_transaction();

    try {
        // Update user details
        $sqlUpdateUser = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
        $stmtUpdateUser = $conn->prepare($sqlUpdateUser);
        $stmtUpdateUser->bind_param("sssi", $name, $email, $role, $userId);
        $stmtUpdateUser->execute();

        // Update address details
        $sqlUpdateAddress = "UPDATE addresses SET street = ?, city = ?, state = ?, country = ? WHERE user_id = ?";
        $stmtUpdateAddress = $conn->prepare($sqlUpdateAddress);
        $stmtUpdateAddress->bind_param("ssssi", $street, $city, $state, $country, $userId);
        $stmtUpdateAddress->execute();

        // Commit the transaction
        $conn->commit();

        // Redirect or display success message
        header("Location: view_all_users.php");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        $error = "Failed to update user details. Please try again.";
    }
} else {
    $error = "Invalid request method.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
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
        select {
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
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <a href="../../index.php">Home</a>
            <a href="../dashboard.php">Dashboard</a>
            <a href="view_all_users.php">Users</a>
            <a href="../products/view_all_products.php">Products</a>
            <a href="../orders/view_all_orders.php">Orders</a>
            <a href="../../prescriptions/view_all.php">Prescriptions</a>
            <a href="../../settings.php">Settings</a>
            <a href="../../includes/logout.php">Log out</a>
        </div>

        <!-- Content -->
        <div class="content">
            <h1>Edit User</h1>

            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="customer" <?php echo $user['role'] == 'customer' ? 'selected' : ''; ?>>Customer</option>
                        <option value="pharmacist" <?php echo $user['role'] == 'pharmacist' ? 'selected' : ''; ?>>Pharmacist</option>
                    </select>
                </div>

                <h2>Address Details</h2>

                <div class="form-group">
                    <label for="street">Street:</label>
                    <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($user['street']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="state">State:</label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($user['state']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="country">Country:</label>
                    <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($user['country']); ?>" required>
                </div>

                <button type="submit" name="submit">Update User</button>
            </form>

        </div>
    </div>
</body>

</html>