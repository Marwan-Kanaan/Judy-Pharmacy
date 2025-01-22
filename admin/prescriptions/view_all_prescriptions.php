<?php
session_start();
include '../../includes/connection.php';

// Check if user_id session exists and role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin role required.');
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of prescriptions per page
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Base query to fetch prescriptions with grouped products
$sql = "
    SELECT p.id, u.name AS customer_name, ph.name AS pharmacist_name, p.status, 
           p.created_at, p.image, p.customer_id_image,
           GROUP_CONCAT(prod.name SEPARATOR ', ') AS products
    FROM prescriptions p
    LEFT JOIN users u ON p.customer_id = u.id
    LEFT JOIN users ph ON p.pharmacist_id = ph.id
    LEFT JOIN prescription_items pi ON p.id = pi.prescription_id
    LEFT JOIN products prod ON pi.product_id = prod.id
    WHERE (p.id LIKE ? OR u.name LIKE ? OR prod.name LIKE ?)
";

// Parameters for search
$params = ["%$search%", "%$search%", "%$search%"];

// Apply status filter
if (!empty($status)) {
    $sql .= " AND p.status = ?";
    $params[] = $status;
}

// Group by prescription ID and add pagination
$sql .= " GROUP BY p.id LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('s', count($params) - 2) . "ii", ...$params);
$stmt->execute();
$result = $stmt->get_result();
$prescriptions = $result->fetch_all(MYSQLI_ASSOC);

// Count total prescriptions for pagination
$totalPrescriptionsQuery = "
    SELECT COUNT(DISTINCT p.id) 
    FROM prescriptions p
    LEFT JOIN users u ON p.customer_id = u.id
    LEFT JOIN prescription_items pi ON p.id = pi.prescription_id
    LEFT JOIN products prod ON pi.product_id = prod.id
    WHERE (p.id LIKE ? OR u.name LIKE ? OR prod.name LIKE ?)
";
$countParams = ["%$search%", "%$search%", "%$search%"];

// Apply status filter for count
if (!empty($status)) {
    $totalPrescriptionsQuery .= " AND p.status = ?";
    $countParams[] = $status;
}

$totalPrescriptionsStmt = $conn->prepare($totalPrescriptionsQuery);
$totalPrescriptionsStmt->bind_param(str_repeat('s', count($countParams)), ...$countParams);
$totalPrescriptionsStmt->execute();
$totalPrescriptionsResult = $totalPrescriptionsStmt->get_result();
$totalPrescriptions = $totalPrescriptionsResult->fetch_row()[0];

$conn->close(); // Close the connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Prescriptions</title>
    <style>
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

        /* Content Area */
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


        .table .actions span.disabled {
            margin-right: 5px;
            text-decoration: none;
            color: #ecf0f1;
            padding: 5px 10px;
            border-radius: 5px;
            background-color: #e74c3c;
        }

        /* Style for the Edit link when disabled */
        .table .actions span.disabled {
            color: gray;
            text-decoration: none;
            cursor: not-allowed;
            background-color: #bdc3c7;
            /* Lighter background color for disabled state */
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
    </style>
    <script>
        // Function to update the URL and search
        function updateURL() {
            let search = document.getElementById('search').value;
            let status = document.getElementById('status').value;
            let url = new URL(window.location.href);
            url.searchParams.set('search', search);
            url.searchParams.set('status', status);
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
        <a href="../orders/view_all_orders.php">Orders</a>
        <a href="view_all_prescriptions.php">Prescriptions</a>
        <a href="../contacts/view_all_contacts.php">Contacts</a>
        <a href="../../includes/logout.php">Log out</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <!-- Navbar -->
        <div class="navbar">
            <div style="display: flex; gap: 10px;">
                <input type="text" id="search" placeholder="Search by prescription ID, customer name, or product name..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="status" id="status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php if ($status === 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="approved" <?php if ($status === 'approved') echo 'selected'; ?>>Approved</option>
                    <option value="rejected" <?php if ($status === 'rejected') echo 'selected'; ?>>Rejected</option>
                </select>
                <button class="search-btn" onclick="updateURL()">Search</button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content">
            <h1>View All Prescriptions</h1>

            <table class="table">
                <tr>
                    <th>Prescription ID</th>
                    <th>Customer Name</th>
                    <th>Products</th>
                    <th>Pharmacist</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Prescription Image</th>
                    <th>Customer ID Image</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($prescriptions as $prescription) { ?>
                    <tr>
                        <td><?php echo $prescription['id']; ?></td>
                        <td><?php echo ucfirst($prescription['customer_name']); ?></td>
                        <td><?php echo ucfirst($prescription['products']); ?></td>
                        <td><?php echo ucfirst($prescription['pharmacist_name']); ?></td>
                        <td><?php echo ucfirst($prescription['status']); ?></td>
                        <td><?php echo $prescription['created_at']; ?></td>
                        <td>
                            <?php if (!empty($prescription['image'])) { ?>
                                <a href="..\..\images\users_uploads\user_prescriptions\<?php echo $prescription['image']; ?>" target="_blank">View</a>
                            <?php } else { ?>
                                N/A
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (!empty($prescription['customer_id_image'])) { ?>
                                <a href="..\..\images\users_uploads\user_id_photo\<?php echo $prescription['customer_id_image']; ?>" target="_blank">View</a>
                            <?php } else { ?>
                                N/A
                            <?php } ?>
                        </td>
                        <td class="actions">
                            <?php if ($prescription['status'] === 'pending') { ?>
                                <a href="edit_prescription.php?id=<?php echo $prescription['id']; ?>" class="edit">Edit</a>
                            <?php } else { ?>
                                <span class="disabled">Edit</span>
                            <?php } ?>
                            <a href="delete_prescription.php?id=<?php echo $prescription['id']; ?>">Delete</a>
                        </td>

                    </tr>
                <?php } ?>
            </table>

            <!-- Pagination Links -->
            <div class="pagination">
                <?php
                $totalPages = ceil($totalPrescriptions / $limit);
                for ($i = 1; $i <= $totalPages; $i++) {
                    echo '<a href="?page=' . $i . ($search ? '&search=' . $search : '') . ($status ? '&status=' . $status : '') . '">' . $i . '</a>';
                }
                ?>
            </div>
        </div>
    </div>

</body>

</html>