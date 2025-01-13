<?php
session_start();
include '../../includes/connection.php';

// Check if user_id session exists and role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin role required.');
}

$user_id = $_SESSION['user_id'];

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of users per page
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';

// Query to fetch users with addresses
$sql = "
    SELECT u.id, u.name, u.email, u.role, a.street, a.city, a.state, a.country 
    FROM users u
    LEFT JOIN addresses a ON u.id = a.user_id
    WHERE u.role != 'admin'
";
$params = [];

// Apply search filter
if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Apply role filter
if (!empty($role)) {
    $sql .= " AND u.role = ?";
    $params[] = $role;
}

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
$users = $result->fetch_all(MYSQLI_ASSOC);

// Count total users with addresses for pagination
$totalUsersQuery = "
    SELECT COUNT(*) 
    FROM users u
    LEFT JOIN addresses a ON u.id = a.user_id
    WHERE u.role != 'admin'
";
$countParams = [];

// Apply search filter
if (!empty($search)) {
    $totalUsersQuery .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.id LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}

// Apply role filter
if (!empty($role)) {
    $totalUsersQuery .= " AND u.role = ?";
    $countParams[] = $role;
}

$totalUsersStmt = $conn->prepare($totalUsersQuery);

if (!empty($countParams)) {
    $countTypes = str_repeat('s', count($countParams));
    $totalUsersStmt->bind_param($countTypes, ...$countParams);
}

$totalUsersStmt->execute();
$totalUsersResult = $totalUsersStmt->get_result();
$totalUsers = $totalUsersResult->fetch_row()[0];

$conn->close(); // Close the connection
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Users</title>
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

        /* Buttons */
        .add-user {
            display: inline-block;
            background-color: #2ecc71;
            color: #ffffff;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 10px;
            transition: background-color 0.3s;
        }

        .add-user:hover {
            background-color: #27ae60;
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
            let role = document.getElementById('role').value;
            let url = new URL(window.location.href);
            url.searchParams.set('search', search);
            url.searchParams.set('role', role);
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
        <a href="view_all_users.php">Users</a>
        <a href="../products/view_all_products.php">Products</a>
        <a href="../../orders/view_all.php">Orders</a>
        <a href="../../prescriptions/view_all.php">Prescriptions</a>
        <a href="../../settings.php">Settings</a>
        <a href="../../includes/logout.php">Log out</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <!-- Navbar -->
        <div class="navbar">
            <div style="display: flex; gap: 10px;">
                <input type="text" id="search" placeholder="Search by name, email or ID..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="role" id="role">
                    <option value="">All Roles</option>
                    <option value="customer" <?php if ($role === 'customer') echo 'selected'; ?>>Customer</option>
                    <option value="pharmacist" <?php if ($role === 'pharmacist') echo 'selected'; ?>>Pharmacist</option>
                </select>
                <button class="search-btn" onclick="updateURL()">Search</button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content">
            <h1>View All Users</h1>

            <!-- Add buttons -->
            <div style="margin-bottom: 20px;">
                <a href="add_user.php" class="add-user">Add User</a>
            </div>

            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Street</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Country</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($users as $user) { ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo ucfirst($user['name']); ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                        <td><?php echo ucfirst($user['street']); ?></td>
                        <td><?php echo ucfirst($user['city']); ?></td>
                        <td><?php echo ucfirst($user['state']); ?></td>
                        <td><?php echo ucfirst($user['country']); ?></td>
                        <td class="actions">
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="edit">Edit</a>
                            <a href="delete_user.php?id=<?php echo $user['id'];
                                                        ?>">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>

            <!-- Pagination Links -->
            <div class="pagination">
                <?php
                $totalPages = ceil($totalUsers / $limit);
                for ($i = 1; $i <= $totalPages; $i++) {
                    echo '<a href="?page=' . $i . ($search ? '&search=' . $search : '') . ($role ? '&role=' . $role : '') . '">' . $i . '</a>';
                }
                ?>
            </div>
        </div>
    </div>

</body>

</html>