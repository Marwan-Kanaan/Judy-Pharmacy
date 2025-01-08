<?php
session_start();
include 'includes/connection.php';

// Fetch products from the database
$sql = "SELECT id, name, price, image_path FROM products LIMIT 12"; // Adjust limit as needed
$result = $conn->query($sql);
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
            height:auto;
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
                    <li><a href="index.php" >Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="products.php" class="active">Products</a></li>
                    <li><a href="login.php">Log In</a></li>
                </ul>
            </nav>
        </header>
    </section>

    <div class="container">
        <h1>Our Products</h1>
        <div class="product-grid">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="product-card">
                    <img src="<?php echo $row['image_path']; ?>" alt="Product Image">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p>$<?php echo number_format($row['price'], 2); ?></p>
                    <div class="actions">
                        <button>
                            <a href="product_details.php?id=<?php echo $row['id']; ?>">View Details</a>
                        </button>
                        <button onclick="addToCart(<?php echo $row['id']; ?>)">Add to Cart</button>
                    </div>
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
                    <li><a href="index.php" >Home</a></li>
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