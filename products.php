<?php
session_start();
include 'includes/connection.php';


$isLoggedIn = isset($_SESSION['user_id']);
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null; // Get the user's role from the session

// Fetch categories for the filter dropdown
$categoriesSql = "SELECT id, name FROM categories";
$categoriesResult = $conn->query($categoriesSql);

// Initialize filter variables
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$categoryId = isset($_GET['category']) && $_GET['category'] != 0 ? (int)$_GET['category'] : null;

// Build the product query with filters
$filteredSql = "SELECT id, name, price, stock,image_path FROM products WHERE 1";

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

// Fetch filtered best-seller products
$bestSellerSql = "
    SELECT p.id, p.name, p.price,p.stock , p.image_path, SUM(od.quantity) AS total_sold
    FROM products p
    JOIN order_details od ON p.id = od.product_id
    JOIN orders o ON od.order_id = o.id
    WHERE 1";

// Apply filters to Best Sellers
if (!empty($searchQuery)) {
    $bestSellerSql .= " AND p.name LIKE '%" . $conn->real_escape_string($searchQuery) . "%'";
}
if ($minPrice !== null) {
    $bestSellerSql .= " AND p.price >= $minPrice";
}
if ($maxPrice !== null) {
    $bestSellerSql .= " AND p.price <= $maxPrice";
}
if ($categoryId !== null) {
    $bestSellerSql .= " AND p.category_id = $categoryId";
}

$bestSellerSql .= "
    GROUP BY p.id
    ORDER BY total_sold DESC
    ";
$bestSellerResult = $conn->query($bestSellerSql);

// Fetch filtered new arrival products
$newItemsSql = "
    SELECT id, name, price, stock , image_path 
    FROM products 
    WHERE created_at >= NOW() - INTERVAL 7 DAY";

// Apply filters to New Arrivals
if (!empty($searchQuery)) {
    $newItemsSql .= " AND name LIKE '%" . $conn->real_escape_string($searchQuery) . "%'";
}
if ($minPrice !== null) {
    $newItemsSql .= " AND price >= $minPrice";
}
if ($maxPrice !== null) {
    $newItemsSql .= " AND price <= $maxPrice";
}
if ($categoryId !== null) {
    $newItemsSql .= " AND category_id = $categoryId";
}

$newItemsSql .= " ORDER BY created_at DESC ";
$newItemsResult = $conn->query($newItemsSql);

// Default products query if no filters are applied
$defaultSql = "SELECT id, name, price, image_path FROM products "; // Adjust limit as needed
$defaultResult = $conn->query($defaultSql);
// Check if customer is logged in
$isCustomerLoggedIn = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer';
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
            max-width: auto;
            margin: 0 auto;

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
            height: 70vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: rgb(245, 245, 245);
            padding-top: 18rem;
            margin-top: 6rem;
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
            max-width: 1200px;
            margin: 0 auto;
            /* Center horizontally */
            margin-bottom: 50px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            /* Center items vertically */
            justify-content: space-between;
            /* Distribute space evenly */
            gap: 25px;
            position: relative;
            /* Allow positioning adjustments */
            top: 50%;
            /* Center vertically */

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

        .product-slideshow {
            position: relative;
            width: 100%;
            height: 20rem;
            overflow: hidden;
            display: flex;
            align-items: center;
            margin-top: 30px;
        }

        .product-slideshow-1 {
            position: relative;
            width: 100%;
            height: 20rem;
            overflow: hidden;
            display: flex;
            align-items: center;
            margin-top: 30px;
        }

        .product-slideshow-2 {
            position: relative;
            width: 100%;
            height: 20rem;
            overflow: hidden;
            display: flex;
            align-items: center;
            margin-top: 30px;
        }

        .product-container {
            display: flex;
            transition: transform 0.5s ease-in-out;
            gap: 13px;
        }

        .product-card {
            flex: 0 0 auto;
            width: 250px;
            text-align: center;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .product-card img {
            width: 100%;
            height: 150px;
            object-fit: contain;
        }

        .slide-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 18px;
            border-radius: 50%;
            z-index: 10;
        }

        .slide-btn:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .prev-btn {
            left: 10px;
        }

        .next-btn {
            right: 10px;
        }

        .slide-btn-1 {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 18px;
            border-radius: 50%;
            z-index: 10;
        }

        .slide-btn-1:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .prev-btn-1 {
            left: 10px;
        }

        .next-btn-1 {
            right: 10px;
        }

        .slide-btn-2 {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 18px;
            border-radius: 50%;
            z-index: 10;
        }

        .slide-btn-2:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .prev-btn-2 {
            left: 10px;
        }

        .next-btn-2 {
            right: 10px;
        }


        .product-card h3 {
            font-size: 18px;
            margin: 10px 0;
            color: #333;
        }

        .product-card p {
            color: #777;
            margin: 0 0 15px;
        }

        .product-card .buttons {
            display: flex;
            justify-content: space-between;
            padding: 10px;
        }

        .product-card .buttons a {
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.3s, color 0.3s;
        }

        .product-card .buttons a.view-details {
            background: #007bff;
            color: white;
        }

        .product-card .buttons a.view-details:hover {
            background: #0056b3;
        }

        .product-card .buttons a.add-to-cart {
            background: #28a745;
            color: white;
        }

        .product-card .buttons a.add-to-cart:hover {
            background: #1e7e34;
        }

        .out-of-stock {
            color: red;
            font-weight: bold;
            margin-top: 8px;
            margin-right: 4px;
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


        footer {
            background-color: rgb(238, 237, 237);
            color: rgb(170, 170, 170);
            padding: 2rem 0;
            text-align: center;
            width: 100%;
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

                    <?php if ($isLoggedIn): ?>
                        <?php if ($userRole === 'customer'): ?>
                            <!-- Display 'Cart' and 'Profile' for customer role -->
                            <li><a href="customer/cart.php">Cart</a></li>
                            <li><a href="customer/profile.php">Profile</a></li>
                            <?php elseif ($userRole === 'admin'): ?>
                            <!-- Display admin-specific options -->
                            <li><a href="admin/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="includes/logout.php">Log Out</a></li>
                    <?php else: ?>
                        <!-- Display 'Log In' if not logged in -->
                        <li><a href="login.php">Log In</a></li>
                    <?php endif; ?>
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



        <h2 class="section-title">Our Products</h2>


        <div class="product-slideshow">
            <button class="slide-btn prev-btn">&#10094;</button>
            <div class="product-container">
                <?php while ($row = $filteredResult->fetch_assoc()) : ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p>$<?php echo htmlspecialchars(number_format($row['price'], 2)); ?></p>
                        <div class="buttons">
                            <a href="product_details.php?id=<?php echo $row['id']; ?>" class="view-details">View Details</a>
                            <?php if ($row['stock'] <= 0) : ?>
                                    <span class="out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            <?php if ($isCustomerLoggedIn) : ?>
                                <?php if ($row['stock'] > 0) : ?>
                                    <a href="customer/add_to_cart.php?id=<?= $row['id'] ?>" class="add-to-cart">Add to Cart</a>
                                <?php else : ?>
                                   
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <button class="slide-btn next-btn">&#10095;</button>
        </div>
        </>


        <h2 class="section-title">Best Sellers</h2>
        <section class="product-slideshow-1">

            <button class="slide-btn-1 prev-btn-1">&#10094;</button>
            <div class="product-container">
                <?php while ($bestSeller = $bestSellerResult->fetch_assoc()) { ?>
                    <div class="product-card">
                        <img src="<?= $bestSeller['image_path'] ?>" alt="<?= $bestSeller['name'] ?>">
                        <h3><?= $bestSeller['name'] ?></h3>
                        <p>$<?= number_format($bestSeller['price'], 2) ?></p>
                        <div class="buttons">
                            <a href="product_details.php?id=<?= $bestSeller['id'] ?>" class="view-details">View Details</a>
                            <?php if ($bestSeller['stock'] <= 0) : ?>
                                    <span class="out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            <?php if ($isCustomerLoggedIn) : ?>
                                <?php if ($bestSeller['stock'] > 0) : ?>
                                    <a href="customer/add_to_cart.php?id=<?= $bestSeller['id'] ?>" class="add-to-cart">Add to Cart</a>
                                <?php else : ?>
                                    
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <button class="slide-btn-1 next-btn-1">&#10095;</button>
        </section>

        <h2 class="section-title">New Arrivals</h2>
        <section class="product-slideshow-2">

            <button class="slide-btn-2 prev-btn-2">&#10094;</button>
            <div class="product-container">
                <?php while ($newItem = $newItemsResult->fetch_assoc()) { ?>
                    <div class="product-card">
                        <img src="<?= $newItem['image_path'] ?>" alt="<?= $newItem['name'] ?>">
                        <h3><?= $newItem['name'] ?></h3>
                        <p>$<?= number_format($newItem['price'], 2) ?></p>
                        <div class="buttons">
                            <a href="product_details.php?id=<?= $newItem['id'] ?>" class="view-details">View Details</a>
                            <?php if ($newItem['stock'] <= 0) : ?>
                                    <span class="out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            <?php if ($isCustomerLoggedIn) : ?>
                                <?php if ($newItem['stock'] > 0) : ?>
                                    <a href="customer/add_to_cart.php?id=<?= $newItem['id'] ?>" class="add-to-cart">Add to Cart</a>
                                <?php else : ?>
                                    
                                <?php endif; ?>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php } ?>
            </div>
            <button class="slide-btn-2 next-btn-2">&#10095;</button>
        </section>



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

                    <?php if ($isLoggedIn): ?>
                        <?php if ($userRole === 'customer'): ?>
                            <!-- Display 'Cart' and 'Profile' for customer role -->
                            <li><a href="customer/cart.php">Cart</a></li>
                            <li><a href="customer/profile.php">Profile</a></li>
                            <?php elseif ($userRole === 'admin'): ?>
                            <!-- Display admin-specific options -->
                            <li><a href="admin/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="includes/logout.php">Log Out</a></li>
                    <?php else: ?>
                        <!-- Display 'Log In' if not logged in -->
                        <li><a href="login.php">Log In</a></li>
                    <?php endif; ?>
                </ul>
                </nav>
            </div>
            <div class="footer-rights">
                <p>&copy; <?= date('Y') ?> Joudi Pharmacy. All Rights Reserved.</p>
            </div>
        </footer>

        <script>
            // Function to move the product slideshow
            function slideShow(slideClass, direction) {
                const slideContainers = document.querySelectorAll(slideClass);

                slideContainers.forEach(slideContainer => {
                    const productContainer = slideContainer.querySelector('.product-container');
                    const productWidth = slideContainer.querySelector('.product-card').offsetWidth;
                    const currentTransformValue = productContainer.style.transform.replace('translateX(', '').replace('px)', '') || 0;
                    const newTransformValue = direction === 'next' ?
                        (parseInt(currentTransformValue) - productWidth) + 'px' :
                        (parseInt(currentTransformValue) + productWidth) + 'px';

                    productContainer.style.transform = `translateX(${newTransformValue})`;
                });
            }

            // Attach events to next and previous buttons foryy product sliders
            document.querySelectorAll('.slide-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const direction = button.classList.contains('next-btn') ? 'next' : 'prev';
                    slideShow('.product-slideshow', direction);
                });
            });

            document.querySelectorAll('.slide-btn-1').forEach(button => {
                button.addEventListener('click', () => {
                    const direction = button.classList.contains('next-btn-1') ? 'next' : 'prev';
                    slideShow('.product-slideshow-1', direction);
                });
            });

            document.querySelectorAll('.slide-btn-2').forEach(button => {
                button.addEventListener('click', () => {
                    const direction = button.classList.contains('next-btn-2') ? 'next' : 'prev';
                    slideShow('.product-slideshow-2', direction);
                });
            });
        </script>


</body>

</html>
<?php
$conn->close();
?>