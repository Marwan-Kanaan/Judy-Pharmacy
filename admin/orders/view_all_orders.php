<?php
session_start();
include '../../includes/connection.php';


// Check if user_id session exists and role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin role required.');
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of orders per page
$offset = ($page - 1) * $limit;

// Search filter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query to fetch orders with customer details
$sql = "SELECT 
            o.*, 
            u.name AS customer_name, 
            u.email AS customer_email
        FROM orders o
        LEFT JOIN users u ON o.customer_id = u.id
        WHERE 1=1";
$params = [];

// Apply search filter
if (!empty($search)) {
    $sql .= " AND (o.id LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Add a query to get the sum of total price for completed orders
$totalCompletedQuery = "SELECT SUM(o.total_price) FROM orders o
                        LEFT JOIN users u ON o.customer_id = u.id
                        WHERE o.status = 'completed'";

// Apply search filter for counting completed orders
if (!empty($search)) {
    $totalCompletedQuery .= " AND (o.id LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $totalOrdersParams[] = "%$search%";
    $totalOrdersParams[] = "%$search%";
    $totalOrdersParams[] = "%$search%";
    $totalOrdersTypes .= 'sss';
}

$totalCompletedStmt = $conn->prepare($totalCompletedQuery);
if (!empty($totalOrdersParams)) {
    $totalCompletedStmt->bind_param($totalOrdersTypes, ...$totalOrdersParams);
}
$totalCompletedStmt->execute();
$totalCompletedResult = $totalCompletedStmt->get_result();
$totalCompletedPrice = $totalCompletedResult->fetch_row()[0] ?? 0;

// Pagination
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$types = str_repeat('s', count($params) - 2) . "ii"; // Dynamic types: 's' for strings, 'ii' for integers
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Count total orders for pagination
$totalOrdersQuery = "SELECT COUNT(*) FROM orders o LEFT JOIN users u ON o.customer_id = u.id WHERE 1=1";
$totalOrdersParams = [];
$totalOrdersTypes = '';

// Apply search filter for counting
if (!empty($search)) {
    $totalOrdersQuery .= " AND (o.id LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $totalOrdersParams[] = "%$search%";
    $totalOrdersParams[] = "%$search%";
    $totalOrdersParams[] = "%$search%";
    $totalOrdersTypes .= 'sss';
}

$totalOrdersStmt = $conn->prepare($totalOrdersQuery);

if (!empty($totalOrdersParams)) {
    $totalOrdersStmt->bind_param($totalOrdersTypes, ...$totalOrdersParams);
}

$totalOrdersStmt->execute();
$totalOrdersResult = $totalOrdersStmt->get_result();
$totalOrders = $totalOrdersResult->fetch_row()[0];

// Handle the generation of the TXT file
if (isset($_POST['generate_txt'])) {
    // Fetch all orders without pagination to get all results
    $sql = "SELECT 
                o.*, 
                u.name AS customer_name, 
                u.email AS customer_email
            FROM orders o
            LEFT JOIN users u ON o.customer_id = u.id";
    $result = $conn->query($sql);

    $txtContent = "Order ID\tCustomer Name\tCustomer Email\tTotal Price\tOrder Date\tLast Update Date\tStatus\tItems\n";

    // Loop through the orders and add them to the TXT content
    while ($order = $result->fetch_assoc()) {
        // Fetching the products for each order
        $orderId = $order['id'];
        $sql_items = "SELECT oi.*, p.name AS product_name FROM order_details oi
                      JOIN products p ON oi.product_id = p.id
                      WHERE oi.order_id = ?";
        $stmt_items = $conn->prepare($sql_items);
        $stmt_items->bind_param('i', $orderId);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();
        $items = [];

        while ($item = $result_items->fetch_assoc()) {
            $items[] = $item['product_name'];
        }

        // Adding the order data to the TXT content
        $txtContent .= $order['id'] . "\t" .
            $order['customer_name'] . "\t" .
            $order['customer_email'] . "\t" .
            number_format($order['total_price'], 2) . "\t" .
            date('d-m-Y', strtotime($order['created_at'])) . "\t" .
            date('d-m-Y', strtotime($order['updated_at'])) . "\t" .
            ucfirst($order['status']) . "\t" .
            implode(", ", $items) . "\n";
    }

    // Set the headers for the download
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="orders_report.txt"');
    echo $txtContent;
    exit; // Stop the script execution after the download starts
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Orders</title>
    <style>
        /* Reset and Base Styles */
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

        /* Top Navbar */
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

        .navbar select,
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

        /* Updated Content Area */
        .content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            /* Enable scrolling for the content area */
            height: calc(100vh - 60px);
            /* Adjust for the height of the navbar */
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

        .table .actions span.edit {
            margin-right: 5px;
            text-decoration: none;
            color: #ecf0f1;
            padding: 5px 10px;
            border-radius: 5px;
            background-color: #e74c3c;
        }

        /* Style for the Edit link when disabled */
        .table .actions span.edit-disabled {
            color: gray;
            text-decoration: none;
            cursor: not-allowed;
            background-color: #bdc3c7;
            /* Lighter background color for disabled state */
        }

        .table .actions a.edit {
            background-color: #3498db;
        }


        .table .actions a:hover {
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

        /* Buttons */
        .add-product {
            display: inline-block;
            background-color: #2ecc71;
            color: #ffffff;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 10px;
            transition: background-color 0.3s;
        }

        .add-product:hover {
            background-color: #27ae60;
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
    </style>
    <script>
        function updateURL() {
            let search = document.getElementById('search').value;
            let url = new URL(window.location.href);
            url.searchParams.set('search', search);
            window.history.pushState({}, '', url);
            window.location.reload();
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
        <a href="view_all_orders.php">Orders</a>
        <a href="../../prescriptions/view_all.php">Prescriptions</a>
        <a href="../../settings.php">Settings</a>
        <a href="../../includes/logout.php">Log out</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <!-- Navbar -->
        <div class="navbar">
            <div style="display: flex; gap: 10px;">
                <input type="text" id="search" placeholder="Search by order ID, customer name, or email..." value="<?php echo htmlspecialchars($search); ?>">
                <button onclick="updateURL()" class="search-btn">Search</button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content">
            <h1>View All Orders</h1>

            <!-- Button to Generate TXT File -->
            <form method="post">
                <button type="submit" name="generate_txt" class="search-btn">Generate TXT</button>
            </form>


            <table class="table">
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Customer Email</th>
                    <th>Total Price</th>
                    <th>Order Date</th>
                    <th>Last update Date</th>
                    <th>Status</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($orders as $order) {
                    // Fetching the products for each order
                    $orderId = $order['id'];
                    $sql_items = "SELECT oi.*, p.name AS product_name, p.price AS product_price FROM order_details oi
                                  JOIN products p ON oi.product_id = p.id
                                  WHERE oi.order_id = ?";
                    $stmt_items = $conn->prepare($sql_items);
                    $stmt_items->bind_param('i', $orderId);
                    $stmt_items->execute();
                    $result_items = $stmt_items->get_result();
                    $items = $result_items->fetch_all(MYSQLI_ASSOC);
                ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo ucfirst($order['customer_name']); ?></td>
                        <td><?php echo $order['customer_email']; ?></td>
                        <td><?php echo number_format($order['total_price'], 2); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($order['created_at'])); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($order['updated_at'])); ?></td>
                        <td><?php echo ucfirst($order['status']); ?></td>
                        <td>
                            <ul>
                                <?php foreach ($items as $item) { ?>
                                    <ol>
                                        <?php echo $item['product_name']; ?> (x<?php echo $item['quantity']; ?>)
                                        - $<?php echo number_format($item['product_price'], 2); ?>
                                    </ol>
                                <?php } ?>
                            </ul>
                        </td>
                        <td class="actions">
                            <!-- Check if the order status is neither 'completed' nor 'cancelled' before allowing editing -->
                            <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                                <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="edit">Edit</a>
                            <?php else: ?>
                                <span class="edit edit-disabled">Edit</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <!-- Add the total completed price below the table -->


                <?php } ?>
                <!-- Add the total completed price below the table -->
                <tr>
                    <td colspan="7" style="text-align: right; font-weight: bold;">Total Price for Completed Orders: </td>
                    <td colspan="2" style="text-align: right; font-weight: bold;">
                        <?php echo number_format($totalCompletedPrice, 2); ?>
                    </td>
                </tr>

            </table>

            <!-- Pagination Links -->
            <div class="pagination">
                <?php
                // Pagination logic (previous/next pages, etc.)
                $totalPages = ceil($totalOrders / $limit);
                for ($i = 1; $i <= $totalPages; $i++) {
                    echo "<a href='view_all_orders.php?page=$i'>$i</a>";
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>