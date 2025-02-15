<?php
session_start(); // Start the session to access logged-in user's data

// Database connection
include '../includes/connection.php';  // Ensure this includes your database connection

// Check if user_id session exists and role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin role required.');
}

$user_id = $_SESSION['user_id'];

// Fetch user profile image and admin name from database
$query = $conn->prepare("SELECT image_path, name FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if ($result->num_rows === 0) {
    die('User not found.');
}

// Fetch product counts by category
$product_categories_query = $conn->query("
    SELECT categories.name AS category, COUNT(*) AS count 
    FROM products 
    JOIN categories ON products.category_id = categories.id 
    GROUP BY categories.name
");
$product_categories = [];
while ($row = $product_categories_query->fetch_assoc()) {
    $product_categories[$row['category']] = $row['count'];
}

// Fetch prescription counts by status
$prescription_status_query = $conn->query("
    SELECT status, COUNT(*) AS count 
    FROM prescriptions 
    GROUP BY status
");
$prescription_status = [];
while ($row = $prescription_status_query->fetch_assoc()) {
    $prescription_status[$row['status']] = $row['count'];
}

$monthly_orders_query = $conn->query("
    SELECT 
        YEAR(created_at) AS year, 
        MONTH(created_at) AS month, 
        COUNT(*) AS count 
    FROM orders 
    GROUP BY year, month
    ORDER BY year, month
");

$monthly_orders = [];
while ($row = $monthly_orders_query->fetch_assoc()) {
    $year = $row['year'];
    $month = $row['month'];
    $count = $row['count'];
    $monthly_orders[$year][$month] = $count;
}

$chart_data = [];
foreach ($monthly_orders as $year => $months) {
    $chart_data[$year] = array_fill(1, 12, 0); // Fill all 12 months with 0
    foreach ($months as $month => $count) {
        $chart_data[$year][$month] = $count;
    }
}


// Fetch statistics for dashboard
$total_users_query = $conn->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE role = 'customer'   
");
$total_users = $total_users_query->fetch_row()[0];

$total_products_query = $conn->query("SELECT COUNT(*) FROM products");
$total_products = $total_products_query->fetch_row()[0];

$total_orders_query = $conn->query("SELECT COUNT(*) FROM orders");
$total_orders = $total_orders_query->fetch_row()[0];

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            width: calc(115% - 250px);
            display: flex;
            align-items: center;
            justify-content: end;
            padding: 0 20px;
        }

        .navbar .search-box {
            background-color: #2c3e50;
            border: none;
            padding: 10px;
            border-radius: 5px;
            color: #ecf0f1;
        }

        .navbar .profile {
            display: flex;
            align-items: center;
        }

        .navbar .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
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

        .content .stats {
            display: flex;
            gap: 20px;
        }

        .stats .card {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 10px;
            flex: 1;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .stats .card h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .stats .card p {
            font-size: 2rem;
            color: #2c3e50;
        }

        .charts {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
            
        }

        .chart-container {
            flex: 1 1 25%;
            min-width: 300px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            justify-content: center;
            align-items: center;
        }

        .chart-container h3 {
            text-align: center;
            margin-bottom: 15px;
            color: #2c3e50;
        }


        canvas {
            max-width: 100%;
            height: 300px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><?php echo ucfirst($user['name']); ?> Panel</h2>
        <a href="../index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="users/view_all_users.php">Users</a>
        <a href="products/view_all_products.php">Products</a>
        <a href="orders/view_all_orders.php">Orders</a>
        <a href="prescriptions/view_all_prescriptions.php">Prescriptions</a>
        <a href="contacts/view_all_contacts.php">Contacts</a>
        <a href="../includes/logout.php">Log out</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <!-- Navbar -->
        <div class="navbar">
            <div class="profile">
                <img src="<?php echo $user['image_path']; ?>" alt="Profile">
                <span><?php echo ucfirst($user['name']); ?></span>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content">
            <h1>Welcome to the <?php echo ucfirst($user['name']); ?> Dashboard</h1>
            <div class="stats">
                <div class="card">
                    <h3>Total Users</h3>
                    <p><?php echo $total_users; ?></p>
                </div>
                <div class="card">
                    <h3>Total Products</h3>
                    <p><?php echo $total_products; ?></p>
                </div>
                <div class="card">
                    <h3>Total Orders</h3>
                    <p><?php echo $total_orders; ?></p>
                </div>
                <div class="card">
                    <h3>Total Prescriptions</h3>
                    <p><?php echo array_sum($prescription_status); ?></p>
                </div>
            </div>

            <div class="charts">
                <div class="chart-container">
                    <h3>Products by Category</h3>
                    <canvas id="productCategoriesChart"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Prescriptions by Status</h3>
                    <canvas id="prescriptionStatusChart"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Monthly Orders</h3>
                    <canvas id="monthlyOrdersChart"></canvas>

                </div>
            </div>
        </div>
    </div>

    <script>
        // PHP data passed to JavaScript
        const productCategoriesData = <?php echo json_encode($product_categories); ?>;
        const prescriptionStatusData = <?php echo json_encode($prescription_status); ?>;


        // Product Categories Chart
        const productCategoriesChartCtx = document.getElementById('productCategoriesChart').getContext('2d');
        new Chart(productCategoriesChartCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(productCategoriesData),
                datasets: [{
                    label: 'Products by Category',
                    data: Object.values(productCategoriesData),
                    backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#9b59b6', '#f1c40f'],
                }]
            }
        });

        // Prescription Status Chart
        const prescriptionStatusChartCtx = document.getElementById('prescriptionStatusChart').getContext('2d');
        new Chart(prescriptionStatusChartCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(prescriptionStatusData),
                datasets: [{
                    label: 'Prescriptions by Status',
                    data: Object.values(prescriptionStatusData),
                    backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#9b59b6', '#f1c40f'],
                }]
            }
        });

        const monthlyOrdersData = <?php echo json_encode($chart_data); ?>;

        // Prepare the datasets dynamically for each year
        const datasets = Object.keys(monthlyOrdersData).map(year => ({
            label: `Year ${year}`,
            data: monthlyOrdersData[year],
            backgroundColor: `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 0.5)`, // Random colors
            borderColor: `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 1)`,
            borderWidth: 1,
        }));

        // Create the chart
        const ctx = document.getElementById('monthlyOrdersChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: Object.keys(monthlyOrdersData).map(year => ({
                    label: `Year ${year}`,
                    data: Object.values(monthlyOrdersData[year]), // Array of 12 numbers
                    backgroundColor: `rgba(${Math.random() * 200}, ${Math.random() * 200}, ${Math.random() * 255}, 0.5)`,
                    borderColor: `rgba(${Math.random() * 200}, ${Math.random() * 200}, ${Math.random() * 255}, 1)`,
                    borderWidth: 1,
                })),
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Months',
                        },
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Orders',
                        },
                    },
                },
            },
        });
    </script>
</body>

</html>