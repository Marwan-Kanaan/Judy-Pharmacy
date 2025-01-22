<?php
session_start();
include 'includes/connection.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null; // Get the user's role from the session

// Check if the 'id' parameter is provided in the URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch the product details from the database, including category and prescription status
    $query = "SELECT products.*, categories.name AS category_name, categories.description AS category_description 
              FROM products 
              LEFT JOIN categories ON products.category_id = categories.id 
              WHERE products.id = '$product_id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
    } else {
        echo "<p>Product not found.</p>";
        exit;
    }
} else {
    echo "<p>Invalid request.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: "Roboto", sans-serif;
            margin: 0;
            padding: 0;
            background-color: rgb(245, 245, 245);
            /* Baby Blue Background */
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            padding: 20px;
        }

        .product-detail {
            display: flex;
            align-items: flex-start;
            gap: 50px;
            margin-top: 15rem;
            /* Increased space between image and info */
        }

        .product-detail img {
            max-width: 1000px;
            /* Increased image size */
            height: auto;
            border-radius: 10px;
        }

        .product-detail .info {
            max-width: 600px;
        }

        .product-detail h2 {
            color: #4A4A4A;
        }

        .product-detail p {
            color: #4A4A4A;
            font-size: 16px;
            line-height: 1.5;
        }

        .add-to-cart {
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            background: #28a745;
            color: white;
            transition: background 0.3s, color 0.3s;
        }

        .add-to-cart:hover {
            background: #1e7e34;

            color: white;
        }

        .add-to-cart:disabled {
            background: #cccccc;
            color: #666666;
            pointer-events: none;
        }

        .add-to-cart:disabled:hover {
            background: #cccccc;
            color: #666666;
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

        .stock-status {
            font-weight: bold;
            color: green;
        }

        .stock-status.out-of-stock {
            color: red;
        }

        .prescription-required {
            font-weight: bold;
            color: #9B1B30;
        }

        .back-to-products {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4A4A4A;
            color: white;
            text-decoration: none;
            font-size: 16px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .back-to-products:hover {
            background-color: #A8C686;
        }
        /* Responsive Design */
@media (max-width: 1200px) {
    .product-detail {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 20px;
    }

    .product-detail img {
        max-width: 80%;
        height: auto;
    }

    .product-detail .info {
        max-width: 90%;
    }
    
}

@media (max-width: 768px) {
    header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    nav ul {
        display: none;
    }

    .container {
        padding: 10px;
    }

    .product-detail {
        margin-top: 10rem;
    }

    .add-to-cart {
        padding: 10px 20px;
        font-size: 16px;
    }

    .footer-nav ul {
            display: block;
            /* Stack the items vertically */
            gap: 1rem;
            /* Adjust gap for better spacing */
        }

        .footer-nav ul li a {
            font-size: 0.9rem;
            /* Smaller font size for mobile */
            padding: 0.5rem 0;
            /* Add padding for better clickability */
        }

        .footer-copy {
            margin-top: 1rem;
            font-size: 0.8rem;
            /* Adjust the font size for smaller screens */
        }
}

@media (max-width: 576px) {
    .product-detail img {
        max-width: 100%;
    }

    .product-detail .info {
        max-width: 100%;
        padding: 0 1rem;
    }

    .back-to-products {
        width: 100%;
        padding: 15px;
        text-align: center;
        font-size: 18px;
    }

    .add-to-cart {
        width: 100%;
        font-size: 18px;
        padding: 15px;
    }

    nav ul {
        display: none;
    }
    
    .footer-nav ul {
            display: block;
            /* Stack the items vertically */
            gap: 1rem;
            /* Adjust gap for better spacing */
        }

        .footer-nav ul li a {
            font-size: 0.9rem;
            /* Smaller font size for mobile */
            padding: 0.5rem 0;
            /* Add padding for better clickability */
        }

        .footer-copy {
            margin-top: 1rem;
            font-size: 0.8rem;
            /* Adjust the font size for smaller screens */
        }
}
    </style>
</head>

<body>

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
                        <li><a href="customer/cart/cart.php">Cart</a></li>
                            <li><a href="customer/profile.php">Profile</a></li>
                            <li><a href="customer/prescriptions/prescriptions.php">Prescriptions</a></li>
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

    <div class="container">
        <div class="product-detail">
            <!-- Left side: Product Image -->
            <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">

            <!-- Right side: Product Info -->
            <div class="info">
                <h2><?php echo $product['name']; ?></h2>
                <p><strong>Price:</strong> $<?php echo $product['price']; ?></p>
                <p><strong>Category:</strong> <?php echo $product['category_name']; ?></p>
                <p><strong>Category Description:</strong> <?php echo $product['category_description']; ?></p>
                <!-- Product Description -->
                <p><strong>Description:</strong> <?php echo $product['description']; ?></p>

                <!-- Stock and Prescription Status -->
                <?php if ($product['stock'] > 0): ?>
                    <!-- If the product is in stock -->
                    <p class="stock-status">Available Item: <?php echo $product['stock']; ?> in stock</p>

                    <?php if ($product['is_prescription_required'] == 1): ?>
                        <!-- If the product requires a prescription -->
                        <p class="prescription-required">Prescription Required</p>
                        <button disabled>Cannot Add to Cart</button>
                    <?php else: ?>
                        <!-- If the product does not require a prescription and is in stock -->
                        <?php if ($isLoggedIn && $userRole === 'customer'): ?>
                            <a href="customer/cart/add_to_cart.php?id=<?php echo $product['id']; ?>" class="add-to-cart">Add to Cart</a>
                        <?php else: ?>
                            <button disabled>Log in to Add to Cart</button>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- If the product is out of stock -->
                    <p class="stock-status out-of-stock">Out of Stock</p>
                    <button disabled>Out of Stock</button>
                <?php endif; ?>

            </div>
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

                    <?php if ($isLoggedIn): ?>
                        <?php if ($userRole === 'customer'): ?>
                            <!-- Display 'Cart' and 'Profile' for customer role -->
                            <li><a href="customer/cart/cart.php">Cart</a></li>
                            <li><a href="customer/profile.php">Profile</a></li>
                            <li><a href="customer/prescriptions/prescriptions.php">Prescriptions</a></li>
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


</body>

</html>