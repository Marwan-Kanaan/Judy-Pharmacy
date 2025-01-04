<?php
session_start();
include '../../includes/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Address data
    $street = $_POST['street'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $country = $_POST['country'];

    // Insert user
    $sqlUser = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bind_param("ssss", $name, $email, $password, $role);
    $stmtUser->execute();
    $userId = $conn->insert_id;

    // Insert address
    $sqlAddress = "INSERT INTO addresses (user_id, street, city, state, country) VALUES (?, ?, ?, ?, ?)";
    $stmtAddress = $conn->prepare($sqlAddress);
    $stmtAddress->bind_param("issss", $userId, $street, $city, $state, $country);
    $stmtAddress->execute();

    // Redirect to view all users
    header("Location: view_all_users.php");
    exit();
}
$conn->close(); // Close the connection
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
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
            width: 95%;
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
            <a href="dashboard.php">Dashboard</a>
            <a href="view_all_users.php">View Users</a>
            <a href="../../products/view_all.php">Products</a>
            <a href="../../orders/view_all.php">Orders</a>
            <a href="../../prescriptions/view_all.php">Prescriptions</a>
            <a href="../../settings.php">Settings</a>
            <a href="../../includes/logout.php">Log out</a>
        </div>


        <!-- Content -->
        <div class="content">
            <h1>Add User</h1>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="customer">Customer</option>
                        <option value="pharmacist">Pharmacist</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="street">Street:</label>
                    <input type="text" id="street" name="street" required>
                </div>

                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" required>
                </div>

                <div class="form-group">
                    <label for="state">State:</label>
                    <input type="text" id="state" name="state" required>
                </div>

                <div class="form-group">
                    <label for="country">Country:</label>
                    <input type="text" id="country" name="country" required>
                </div>

                <button type="submit">Add User</button>
            </form>
        </div>
    </div>
</body>

</html>