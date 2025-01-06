<?php
session_start();
include '../../includes/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of products per page
$offset = ($page - 1) * $limit;

// Search filter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query to fetch products with category details
$sql = "SELECT 
            p.*, 
            c.name AS category_name, 
            c.description AS category_description 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1";
$params = [];

// Apply search filter
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
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
$products = $result->fetch_all(MYSQLI_ASSOC);

// Count total products for pagination
$totalProductsQuery = "SELECT COUNT(*) FROM products p";
$totalProductsParams = [];

// Apply search filter for counting
if (!empty($search)) {
    $totalProductsQuery .= " WHERE p.name LIKE ? OR p.description LIKE ?";
    $totalProductsParams[] = "%$search%";
    $totalProductsParams[] = "%$search%";
}

$totalProductsStmt = $conn->prepare($totalProductsQuery);

if (!empty($totalProductsParams)) {
    $totalCountTypes = str_repeat('s', count($totalProductsParams));
    $totalProductsStmt->bind_param($totalCountTypes, ...$totalProductsParams);
}

$totalProductsStmt->execute();
$totalProductsResult = $totalProductsStmt->get_result();
$totalProducts = $totalProductsResult->fetch_row()[0];

$conn->close(); // Close the connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Products</title>
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
            padding: 15px;
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
    </style>
    <script>
        // Function to update the URL and search
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
        <a href="../../admin/dashboard.php">Dashboard</a>
        <a href="../users/view_all_users.php">Users</a>
        <a href="view_all_products.php">Products</a>
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
                <input type="text" id="search" placeholder="Search by name or description..." value="<?php echo htmlspecialchars($search); ?>">
                <button onclick="updateURL()" class="search-btn">Search</button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content">
            <h1>View All Products</h1>

            <!-- Add buttons -->
            <div style="margin-bottom: 20px;">
                <a href="add_product.php" class="add-product">Add Product</a>
            </div>

            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Category Name</th>
                    <th>Category Description</th>
                    <th>Quantity</th>
                    <th>Image</th>
                    <th>Prescription Required</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($products as $product) { ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo ucfirst($product['name']); ?></td>
                        <td><?php echo $product['description']; ?></td>
                        <td><?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo ucfirst($product['category_name']); ?></td>
                        <td><?php echo $product['category_description']; ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td>
                            <?php if (!empty($product['image_path'])) { ?>
                                <img src="../../uploads/<?php echo $product['image_path']; ?>" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php } else { ?>
                                No Image
                            <?php } ?>
                        </td>
                        <td>
                            <?php echo $product['is_prescription_required'] ? 'Yes' : 'No'; ?>
                        </td>
                        <td class="actions">
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="edit">Edit</a>
                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="delete">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>

            <!-- Pagination Links -->
            <div class="pagination">
                <?php
                $totalPages = ceil($totalProducts / $limit);
                for ($i = 1; $i <= $totalPages; $i++) {
                    echo '<a href="?page=' . $i . ($search ? '&search=' . $search : '') . '">' . $i . '</a>';
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>
