<?php
session_start();
include 'includes/connection.php';

// Fetch categories for the filter dropdown
$categoriesSql = "SELECT id, name FROM categories";
$categoriesResult = $conn->query($categoriesSql);

// Initialize filter variables
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$categoryId = isset($_GET['category']) && $_GET['category'] != 0 ? (int)$_GET['category'] : null;

// Build the product query with filters
$filteredSql = "SELECT id, name, price, image_path FROM products WHERE 1";

// Apply search filter if provided
if (!empty($searchQuery)) {
    $filteredSql .= " AND name LIKE '%" . $conn->real_escape_string($searchQuery) . "%'";
}

// Apply minimum price filter if provided
if ($minPrice !== null) {
    $filteredSql .= " AND price >= $minPrice";
}

// Apply maximum price filter if provided
if ($maxPrice !== null) {
    $filteredSql .= " AND price <= $maxPrice";
}

// Apply category filter if provided
if ($categoryId !== null) {
    $filteredSql .= " AND category_id = $categoryId";
}

// Fetch filtered products
$filteredResult = $conn->query($filteredSql);

// Fetch best-seller products
$bestSellerSql = "
    SELECT p.id, p.name, p.price, p.image_path, SUM(od.quantity) AS total_sold
    FROM products p
    JOIN order_details od ON p.id = od.product_id
    JOIN orders o ON od.order_id = o.id
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5";
$bestSellerResult = $conn->query($bestSellerSql);

// Fetch new items (created within the last week)
$newItemsSql = "
    SELECT id, name, price, image_path 
    FROM products 
    WHERE created_at >= NOW() - INTERVAL 7 DAY
    ORDER BY created_at DESC
    LIMIT 5";
$newItemsResult = $conn->query($newItemsSql);

// Default products query if no filters are applied
$defaultSql = "SELECT id, name, price, image_path FROM products LIMIT 12"; // Adjust limit as needed
$defaultResult = $conn->query($defaultSql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <style>
        body {
            font-family: "Roboto", sans-serif;
            margin: 0;
            padding: 0;
            background-color: rgb(245, 245, 245);
            /* Baby Blue Background */
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 97%;
            padding: 1rem 2rem;
            background-color: rgba(253, 253, 253, 1);
            /* Transparent White */
            color: rgb(117, 117, 117);
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            /* Logo on left, Nav on right */
            align-items: center;
            box-shadow: 0 10px 10px rgba(0, 0, 0, 0.2);
            /* Subtle shadow for effect */
        }

        .logo-image {
            width: 130px;
            /* Adjust the width as needed */
            height: auto;
            /* Maintain aspect ratio */
            object-fit: f;
            /* Ensure the image fits within the given dimensions */
        }

        nav ul {
            list-style: none;
            padding: 0;
            font-size: large;
            display: flex;
            gap: 3rem;
            /* Adjust spacing */
        }

        nav ul li a {
            text-decoration: none;
            color: rgb(117, 117, 117);
            font-weight: bold;
            transition: color 0.3s ease, transform 0.2s ease;
        }

        nav ul li a:hover {
            color: #0288d1;
            transform: scale(1.05);
            /* Small scale effect */
        }

        nav ul li a.active {
            color: #0277bd;
            /* Change color to a specific color, e.g., blue */
            font-weight: bold;
            /* Make the active link bold */
            text-decoration: none;
            /* Add an underline effect */
            transition: all 0.3s ease;
        }

        nav ul li a.active:hover {
            color: #01579b;
            /* A darker shade on hover */
        }


        .hero {
            background: url("images/static/backgrounds/productsback.jpg") center/cover no-repeat;
            height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: rgb(245, 245, 245);
            padding-top: 18rem;
            /* Adjusting padding for fixed navbar */
        }

        .hero-content {
            text-align: center;
        }

        .hero-content h2 {
            font-size: 3rem;
            /* Increased font size */
            margin: 0 0 1rem;
        }

        .hero-content p {
            font-size: 1.2rem;
            /* Adjusted description font size */
            margin-bottom: 2rem;
        }

        .filter-row {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 25px;
        }

        .filter-row input[type="text"],
        .filter-row input[type="number"],
        .filter-row select {
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .filter-row button {
            padding: 10px 15px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .filter-row button:hover {
            background-color: #2980b9;
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

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 50px;
            margin-top: 10dvb;
        }

        .product-card {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .product-card h3 {
            font-size: 18px;
            margin: 10px 0;
        }

        .product-card p {
            font-size: 16px;
            color: #3498db;
            font-weight: bold;
        }

        .product-card button {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .product-card button:hover {
            background-color: #2980b9;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .actions a {
            text-decoration: none;
            color: white;
        }

        .section-title {
            font-size: 24px;
            margin: 20px 0;
            text-align: center;
            color: #3498db;
        }

        .product-section {
            margin-bottom: 40px;
        }

        footer {
            background-color: rgb(238, 237, 237);
            color: rgb(170, 170, 170);
            padding: 2rem 0;
            text-align: center;
        }

        .footer-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .footer-logo h1 {
            margin: 0;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .footer-nav ul {
            list-style: none;
            padding: 0;
            display: flex;
            gap: 1.5rem;
        }

        .footer-nav ul li a {
            text-decoration: none;
            color: rgb(117, 117, 117);
            font-weight: bold;
        }

        .footer-nav ul li a:hover {
            color: #0288d1;
        }

        .footer-copy {
            margin-top: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

    <section class="hero">
        <header>
            <div class="logo">
                <img src="images/static/backgrounds/logo.png" alt="Joudi Pharmacy Logo" class="logo-image">
            </div>

            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="products.php" class="active">Products</a></li>
                    <li><a href="login.php">Log In</a></li>
                </ul>
            </nav>
        </header>
    </section>

    <div class="container">
      
        
               <!-- Filter Row -->
               <div class="filter-row">
            <!-- Search Form -->
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit">Search</button>
            </form>

            <!-- Price Range Form -->
            <form method="GET" action="">
                <input type="number" name="min_price" placeholder="Min Price" step="0.01" value="<?php echo $minPrice !== null ? $minPrice : ''; ?>">
                <input type="number" name="max_price" placeholder="Max Price" step="0.01" value="<?php echo $maxPrice !== null ? $maxPrice : ''; ?>">
                <button type="submit">Filter Price</button>
            </form>

            <!-- Category Filter Form -->
            <form method="GET" action="">
                <select name="category">
                    <option value="0">All Categories</option>
                    <?php while ($category = $categoriesResult->fetch_assoc()) { ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $categoryId == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php } ?>
                </select>
                <button type="submit">Filter Category</button>
            </form>
        </div>


        <h1>Our Products</h1>

        <div class="product-grid">
            <?php 
            if (isset($_GET['search']) || isset($_GET['min_price']) || isset($_GET['max_price']) || isset($_GET['category'])) {
                // Display filtered products
                if ($filteredResult->num_rows > 0) {
                    while ($row = $filteredResult->fetch_assoc()) { ?>
                        <div class="product-card">
                            <img src="<?php echo $row['image_path']; ?>" alt="Product Image">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p>$<?php echo number_format($row['price'], 2); ?></p>
                        </div>
                    <?php }
                } else {
                    echo "<p>No products found matching your criteria.</p>";
                }
            } else {
                // Display default products
                while ($row = $defaultResult->fetch_assoc()) { ?>
                    <div class="product-card">
                        <img src="<?php echo $row['image_path']; ?>" alt="Product Image">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p>$<?php echo number_format($row['price'], 2); ?></p>
                    </div>
                <?php }
            }
            ?>
        </div>

        <h2>Best Sellers</h2>
        <div class="product-grid">
            <?php while ($row = $bestSellerResult->fetch_assoc()) { ?>
                <div class="product-card">
                    <img src="<?php echo $row['image_path']; ?>" alt="Best Seller Image">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p>$<?php echo number_format($row['price'], 2); ?></p>
                </div>
            <?php } ?>
        </div>

        <h2>New Arrivals</h2>
        <div class="product-grid">
            <?php while ($row = $newItemsResult->fetch_assoc()) { ?>
                <div class="product-card">
                    <img src="<?php echo $row['image_path']; ?>" alt="New Arrival Image">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p>$<?php echo number_format($row['price'], 2); ?></p>
                </div>
            <?php } ?>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <h1>Judy Pharmacy</h1>
            </div>

            <nav class="footer-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="products.php" class="active">Products</a></li>
                    <li><a href="login.php">Log In</a></li>
                </ul>
            </nav>
        </div>
        <div class="footer-rights">
            <p>&copy; <?= date('Y') ?> Joudi Pharmacy. All Rights Reserved.</p>
        </div>
    </footer>

</body>

</html>
<?php
$conn->close();
?>