<?php
session_start();
// Include database connection
include 'includes/connection.php';

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null; // Get the user's role from the session

// Fetch categories from the database
$categories = [];
$query = "SELECT name, description, image_path FROM categories";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
}

// Close database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Judy Pharmacy</title>
</head>

<style>
    /* Base styles */

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

    /* Responsive Styles */
    @media (max-width: 768px) {
        nav ul {
            display: none;
            /* Hide the entire menu on small screens */
        }

        header {
            justify-content: center;
        }
    }


    .hero {
        background: url("images/static/backgrounds/hero-image.jpg") center/cover no-repeat;
        height: 90vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        color: rgb(245, 245, 245);
        padding-top: 4rem;
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

    .btn {
        background-color: #0288d1;
        /* Blue Button */
        color: white;
        padding: 0.7rem 1.5rem;
        border-radius: 4px;
        text-decoration: none;
        transition: background-color 0.3s ease, box-shadow 0.2s ease;
    }

    .btn:hover {
        background-color: #0277bd;
        /* Darker Blue on Hover */
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
    }

    .welcome {
        background-color: rgb(245, 245, 245);
        padding: 5rem 2rem;
        text-align: center;
    }

    .welcome .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .welcome h2 {
        font-weight: bold;
        font-size: 1.6rem;
        margin-bottom: 3rem;
    }

    .services {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
    }

    .card {
        background-color: #0288d1;
        color: white;
        padding: 1.5rem;
        border-radius: 8px;
        width: 18%;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin: 1rem 0;
        text-align: center;
    }

    .card h3 {
        margin-bottom: 0.5rem;
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

    /* Specific styles for welcome section */
    .welcome {
        background-image: url("images/static/backgrounds/pattern1.png");
        /* Replace with your image path */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-color: transparent;
        padding: 4rem 10rem;
        text-align: center;
        position: relative;
        /* Required for the background overlay */
        color: #0288d1;
    }

    .welcome::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        /* Light overlay on image */
        z-index: 1;
    }

    .welcome .container {
        position: relative;
        /* Needed to place content correctly */
        z-index: 2;
        /* Above overlay */
    }

    .services {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
    }

    .card {
        background-color: rgba(255, 255, 255, 0.3);
        /* Glass effect with transparency */
        color: #4a4a4a;
        padding: 1.5rem;
        border-radius: 8px;
        width: 18%;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin: 1rem 0;
        text-align: center;
        backdrop-filter: blur(10px);
        /* Glass-like blur effect */
        position: relative;
        /* Required for logo placeholder */
    }

    .card h3 {
        margin-bottom: 0.5rem;
    }

    .card img.logo-placeholder {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 10%;
        /* Placeholder background */
        margin-bottom: 0.5rem;
    }

    .categories {
        background-color: rgb(250, 248, 248);
        padding: 3rem 2rem;
        text-align: center;
    }

    .categories h2 {
        font-size: 2rem;
        margin-bottom: 2rem;
        color: #0288d1;
    }

    .category-list {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .category-card {
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        width: 18%;
        /* Smaller card width */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
        padding: 0.8rem;
        /* Reduced padding */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .category-card img {
        width: 100%;
        height: 100px;
        /* Smaller image height */
        object-fit: contain;
        border-radius: 8px;
    }

    .category-card h3 {
        font-size: 1rem;
        /* Reduced title size */
        margin: 0.5rem 0;
        color: #4a4a4a;
    }

    .category-card p {
        font-size: 0.8rem;
        /* Smaller description size */
        color: #6c757d;
    }

    .category-card:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
    }

    .bmi-calculator {
        background-color: rgb(245, 245, 245);
        padding: 3rem 2rem;
        border-top: 1px solid #ddd;
    }

    .bmi-calculator h2 {
        text-align: center;
        font-size: 2rem;
        color: #0288d1;
        margin-bottom: 2rem;
    }

    .bmi-content {
        display: flex;
        flex-wrap: wrap;
        gap: 15rem;
        justify-content: center;
        align-items: flex-start;
    }

    .bmi-form,
    .bmi-description {
        flex: 1;
        min-width: 300px;
        max-width: 500px;
    }

    .bmi-form h3,
    .bmi-description h3 {
        font-size: 1.5rem;
        color: #4a4a4a;
        margin-bottom: 1rem;
    }

    .bmi-form .input-group {
        margin-bottom: 1.5rem;
    }

    .bmi-form .input-group label {
        display: block;
        font-size: 1rem;
        margin-bottom: 0.5rem;
        color: #4a4a4a;
    }

    .bmi-form .input-group input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        outline: none;
    }

    .bmi-form .btn {
        background-color: #0288d1;
        color: white;
        padding: 0.7rem 1.5rem;
        border-radius: 4px;
        text-decoration: none;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s ease, box-shadow 0.2s ease;
        border: none;
    }

    .bmi-form .btn:hover {
        background-color: #0277bd;
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
    }

    .bmi-result {
        margin-top: 1.5rem;
        font-size: 1.2rem;
        font-weight: bold;
        color: #4a4a4a;
    }

    .bmi-description p {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .text-center {
        text-align: center;
        color: #0288d1;
        font-size: 2rem;
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
        flex-wrap: wrap;
        /* Allow items to wrap on smaller screens */
        justify-content: center;
        /* Center the items */
    }

    .footer-nav ul li a {
        text-decoration: none;
        color: rgb(117, 117, 117);
        font-weight: bold;
        font-size: 1rem;
        /* Adjust font size for better readability */
        margin: 0.5rem 0;
        /* Add spacing between items */
    }

    .footer-nav ul li a:hover {
        color: #0288d1;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
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


    /* Animation Keyframes */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes zoomIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Applying Animations */
    .welcome {
        animation: fadeIn 2s ease-out;
        animation-delay: 2s;
    }

    .category-card {
        animation: zoomIn 2s ease-in-out;
        animation-delay: 0.4s;
    }

    .bmi-calculator {
        animation: fadeIn 4s ease-in-out;
        animation-delay: 0.7s;
    }

    .map-container {
        animation: slideIn 1.5s ease-out;
        animation-delay: 0.4s;
    }

    .animated-section {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 1s ease, transform 1s ease;
    }

    .animated-section.animate {
        opacity: 1;
        transform: translateY(0);
    }



    @media (max-width: 768px) {
        .card {
            width: 45%;
        }

        .category-card {
            width: 45%;
        }

        .bmi-content {
            gap: 2rem;
        }
    }

    @media (max-width: 480px) {

        .card,
        .category-card {
            width: 100%;
        }

        .bmi-form,
        .bmi-description {
            min-width: 100%;
        }
    }
</style>

<body>
    <!-- Hero Section -->
    <section class="hero">
        <header>
            <div class="logo">
                <img src="images/static/backgrounds/logo.png" alt="Joudi Pharmacy Logo" class="logo-image">
            </div>

            <nav>
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="products.php">Products</a></li>

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

        <div class="hero-content">
            <h2>Your Health, Our Priority</h2>
            <p>Find quality medications and healthcare products at Joudi Pharmacy. Your well-being is our mission.</p>
            <a href="products.php" class="btn">Shop Now</a>
        </div>
    </section>

    <!-- Welcome Section -->
    <section class="welcome">
        <div class="container">
            <h2>Welcome to Joudi Pharmacy – your trusted partner for quality medications and personalized healthcare services.</h2>

            <!-- Service Cards -->
            <div class="services">
                <div class="card">
                    <img src="images/static/backgrounds/consulting.png" alt="Consultation Logo" class="logo-placeholder">
                    <h3>Consultation</h3>
                    <p>Expert advice and guidance from licensed pharmacists.</p>
                </div>
                <div class="card">
                    <img src="images/static/backgrounds/prescription.png" alt="Prescription Logo" class="logo-placeholder">
                    <h3>Prescription Services</h3>
                    <p>Efficient prescription filling and management.</p>
                </div>
                <div class="card">
                    <img src="images/static/backgrounds/delivery.png" alt="Home Delivery Logo" class="logo-placeholder">
                    <h3>Home Delivery</h3>
                    <p>Convenient home delivery of medications and products.</p>
                </div>
                <div class="card">
                    <img src="images/static/backgrounds/syringe.png" alt="Vaccination Logo" class="logo-placeholder">
                    <h3>Vaccination</h3>
                    <p>Safe and professional vaccination services.</p>
                </div>
                <div class="card">
                    <img src="images/static/backgrounds/medical-checkup.png" alt="Health Logo" class="logo-placeholder">
                    <h3>Health Checkups</h3>
                    <p>Comprehensive health checkup packages and screenings.</p>
                </div>
                <div class="card">
                    <img src="images/static/backgrounds/shopping-bag.png" alt="Product Shopping Logo" class="logo-placeholder">
                    <h3>Product Shopping</h3>
                    <p>Wide variety of healthcare and wellness products available for purchase.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="categories animated-section">
        <section class="categories">
            <div class="container">
                <h2>Our Categories</h2>
                <div class="category-list">
                    <?php if (!empty($categories)) : ?>
                        <?php foreach ($categories as $category) : ?>
                            <div class="category-card">
                                <img src="<?= htmlspecialchars($category['image_path']) ?>" alt="<?= htmlspecialchars($category['name']) ?>">
                                <h3><?= htmlspecialchars($category['name']) ?></h3>
                                <p><?= htmlspecialchars($category['description']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>No categories available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <div class="bmi-calculator animated-section">
        <section class="bmi-calculator">
            <div class="container">
                <h2>BMI Calculator</h2>
                <div class="bmi-content">
                    <!-- Left side: Calculator -->
                    <div class="bmi-form">
                        <form id="bmi-form">
                            <h3>Calculate Your BMI</h3>
                            <div class="input-group">
                                <label for="weight">Weight (kg):</label>
                                <input type="number" id="weight" name="weight" placeholder="Enter your weight" required>
                            </div>
                            <div class="input-group">
                                <label for="height">Height (cm):</label>
                                <input type="number" id="height" name="height" placeholder="Enter your height" required>
                            </div>
                            <button type="button" class="btn" id="calculate-bmi">Calculate BMI</button>
                        </form>
                        <div id="bmi-result" class="bmi-result">
                            <!-- The result will be displayed here -->
                        </div>
                    </div>

                    <!-- Right side: Description -->
                    <div class="bmi-description">
                        <h3>Description</h3>
                        <p>
                            BMI is a useful measurement for most people over 18 years old.
                            But it is only an estimate and it doesn't take into account age,
                            ethnicity, gender, and body composition. We recommend you also
                            check your waist measurement and other risk factors.
                        </p>
                        <p>
                            Speak to your doctor, an Accredited Practising Dietitian, or a
                            health practitioner about your weight.
                        </p>
                        <p>
                            This calculator shouldn’t be used for pregnant women or children.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="map-container animated-section">
        <section class="map-section">
            <div class="container">
                <h2 class="text-center">Find Us Here</h2>
                <div class="map-container">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3290.3718619570564!2d35.82484297630349!3d34.442707197011174!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1521f77e073ad99f%3A0xf4b999ec60f24a42!2sPHARMACY%20JUDY!5e0!3m2!1sen!2slb!4v1735504213069!5m2!1sen!2slb"
                        width="100%"
                        height="400"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </section>
    </div>



    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <h1>Judy Pharmacy</h1>
            </div>

            <nav class="footer-nav">
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="products.php">Products</a></li>

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

    <script>
        document.getElementById('calculate-bmi').addEventListener('click', function() {
            const weight = parseFloat(document.getElementById('weight').value);
            const height = parseFloat(document.getElementById('height').value) / 100; // Convert cm to meters

            if (isNaN(weight) || isNaN(height) || weight <= 0 || height <= 0) {
                document.getElementById('bmi-result').textContent = "Please enter valid weight and height values.";
                return;
            }

            const bmi = (weight / (height * height)).toFixed(2); // Calculate BMI

            let category = '';
            if (bmi < 18.5) {
                category = 'Underweight';
            } else if (bmi >= 18.5 && bmi < 24.9) {
                category = 'Normal weight';
            } else if (bmi >= 25 && bmi < 29.9) {
                category = 'Overweight';
            } else {
                category = 'Obese';
            }

            document.getElementById('bmi-result').textContent = `Your BMI is ${bmi} (${category}).`;
        });

        document.addEventListener("DOMContentLoaded", function() {
            const animatedSections = document.querySelectorAll('.animated-section');

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.2
            });

            animatedSections.forEach(section => {
                observer.observe(section);
            });
        });
    </script>


</body>

</html>