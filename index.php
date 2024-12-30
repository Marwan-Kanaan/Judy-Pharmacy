<?php
// Include database connection
include 'includes/connection.php';

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
    <link rel="stylesheet" id="theme-stylesheet" href="assets/css/index.css">
</head>

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
                    <li><a href="login.php">Log In</a></li>
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
                    <li><a href="login.php">Log In</a></li>
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

        // Function to toggle the stylesheet
        const themeToggle = document.getElementById('theme-toggle');

        // On change, update the stylesheet href
        themeToggle.addEventListener('change', () => {
            const themeStylesheet = document.getElementById('theme-stylesheet');

            if (themeToggle.checked) {
                themeStylesheet.href = 'assets/css/index-black.css';
            } else {
                themeStylesheet.href = 'assets/css/index.css';
            }
        });
    </script>


</body>

</html>