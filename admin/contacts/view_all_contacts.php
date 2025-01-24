<?php
session_start();
include '../../includes/connection.php';

// Check if user_id session exists and role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin role required.');
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of messages per page
$offset = ($page - 1) * $limit;

// Search variable
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query to fetch contact messages along with user information
$sql = "
    SELECT 
        cm.id AS message_id, 
        cm.customer_message, 
        cm.admin_message, 
        cm.created_at, 
        u.name AS customer_name, 
        u.email AS customer_email, 
        u.phone_number AS customer_phone
    FROM contact_message cm
    INNER JOIN users u ON cm.customer_id = u.id
    WHERE 
        u.name LIKE ? 
        OR u.email LIKE ? 
        OR cm.customer_message LIKE ? 
        OR cm.admin_message LIKE ?
    LIMIT ? OFFSET ?
";
$params = ["%$search%", "%$search%", "%$search%", "%$search%", $limit, $offset];

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssii", ...$params);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

// Count total contact messages for pagination
$totalMessagesQuery = "
    SELECT COUNT(*) 
    FROM contact_message cm
    INNER JOIN users u ON cm.customer_id = u.id
    WHERE 
        u.name LIKE ? 
        OR u.email LIKE ? 
        OR cm.customer_message LIKE ? 
        OR cm.admin_message LIKE ?
";
$countParams = ["%$search%", "%$search%", "%$search%", "%$search%"];
$countStmt = $conn->prepare($totalMessagesQuery);
$countStmt->bind_param("ssss", ...$countParams);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalMessages = $countResult->fetch_row()[0];

$conn->close(); // Close the connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Contacts</title>
    <style>
        /* Common styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
            background-color: #f8f9fa;
        }

        /* Sidebar */
        .sidebar {
            background-color: #2c3e50;
            color: #ecf0f1;
            width: 238px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            margin-bottom: 20px;
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

        /* Navbar */
        .navbar {
            background-color: #34495e;
            color: #ecf0f1;
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            justify-content: space-between;
            width: 500%;
        }

        .navbar input {
            background-color: #2c3e50;
            color: #ecf0f1;
            border: none;
            padding: 10px;
            border-radius: 5px;
        }

        .search-btn {
            background-color: #3498db;
            color: #ffffff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-btn:hover {
            background-color: #2980b9;
        }

        /* Content Area */
        .content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            height: calc(100vh - 60px);
        }

        .content h1 {
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .table th {
            background-color: #2c3e50;
            color: #ecf0f1;
        }

        .table .actions a {
            margin-right: 5px;
            text-decoration: none;
            color: #ecf0f1;
            padding: 5px 10px;
            border-radius: 5px;
            background-color: #e74c3c;
        }

        .table .actions a:hover {
            background-color: #34495e;
        }

        .table .actions a.edit {
            background-color: #3498db;
        }

        .table .actions a.edit:hover {
            background-color: #34495e;
        }


        /* Pagination */
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .pagination a {
            padding: 5px 10px;
            background-color: #2c3e50;
            color: #ecf0f1;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .pagination a:hover {
            background-color: #34495e;
        }
    </style>
     <script>
        // Function to update the URL and search
        function updateURL() {
            const search = document.getElementById('search').value;
            const url = new URL(window.location.href);
            url.searchParams.set('search', search);
            window.location.href = url.toString();
        }
    </script>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="../../index.php">Home</a>
        <a href="../../admin/dashboard.php">Dashboard</a>
        <a href="../users/view_all_users.php">Users</a>
        <a href="../products/view_all_products.php">Products</a>
        <a href="../orders/view_all_orders.php">Orders</a>
        <a href="../prescriptions/view_all_prescriptions.php">Prescriptions</a>
        <a href="view_all_contacts.php">Contacts</a>
        <a href="../../includes/logout.php">Log out</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <!-- Navbar -->
        <div class="navbar">
            <div style="display: flex; gap: 10px;">
                <input type="text" id="search" placeholder="Search by customer name, email, or message..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="search-btn" onclick="updateURL()">Search</button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content">
            <h1>View All Contacts</h1>

            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Customer Message</th>
                    <th>Admin Message</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($messages as $message) { ?>
                    <tr>
                        <td><?php echo $message['message_id']; ?></td>
                        <td><?php echo htmlspecialchars($message['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($message['customer_email']); ?></td>
                        <td><?php echo htmlspecialchars($message['customer_phone']); ?></td>
                        <td><?php echo htmlspecialchars($message['customer_message']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($message['admin_message'])); ?></td>
                        <td><?php echo $message['created_at']; ?></td>
                        <td class="actions">
                            <a href="reply_contact.php?id=<?php echo $message['message_id']; ?> " class="edit" >Reply</a>
                            <a href="delete_contact.php?id=<?php echo $message['message_id']; ?>">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>

            <!-- Pagination Links -->
            <div class="pagination">
                <?php
                $totalPages = ceil($totalMessages / $limit);
                for ($i = 1; $i <= $totalPages; $i++) {
                    echo '<a href="?page=' . $i . '&search=' . urlencode($search) . '">' . $i . '</a>';
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>