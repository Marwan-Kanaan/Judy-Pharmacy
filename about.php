<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
</head>

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

    .overview {
        background-color: rgb(252, 252, 252);
        padding: 4rem 2rem;
        text-align: center;
        color: #4a4a4a;
    }

    .overview h2 {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #0288d1;
    }

    .overview p {
        font-size: 1rem;
        color: #6c757d;
    }

    /* Photo Section with Counters */
    .photo-section {
        position: relative;
        text-align: center;
        color: white;
    }

    .photo-section img {
        width: 100%;
        height: 74dvb;
        filter: blur(50%);
        object-fit: cover;

    }

    .counter-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: flex;
        justify-content: space-around;
        width: 80%;
        color: #ffffff;
    }

    .counter-item {
        text-align: center;
    }

    .counter-item h2 {
        font-size: 3rem;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .counter-item p {
        font-size: 1.2rem;
    }

    /* Plus sign style */
    .plus-sign {
        color: rgb(255, 255, 255);
        font-size: 2rem;
        margin-right: 5px;
    }



    .about-overview {
        background-image: url("images/static/backgrounds/pattern2.png");
        /* Replace with your image path */
        background-size: contain;
        background-position: center;
        background-repeat:repeat-x;
        background-color: transparent;
        padding: 4rem 2rem;
        text-align: center;
        color: #4a4a4a;
    }

    .overview-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .overview-services {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        gap: 2rem;
    }

    .overview-card {
        background-color: #ffffff;
        padding: 2rem;
        border-radius: 10px;
        width: 45%;
        box-shadow: 0 8px 8px rgba(0, 0, 0, 0.2);
        text-align: center;
    }

    .overview-card h2 {
        font-size: 1.8rem;
        color: #0288d1;
        margin-bottom: 1rem;
    }

    .overview-card p {
        font-size: 1rem;
        color: #4a4a4a;
        line-height: 1.6;
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

<body>
    <section>
        <header>
            <div class="logo">
                <img src="images/static/backgrounds/logo.png" alt="Joudi Pharmacy Logo" class="logo-image">
            </div>

            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php" class="active">About Us</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="login.php">Log In</a></li>
                </ul>
            </nav>
        </header>
        <br><br><br><br>

    </section>

    <section class="overview">
        <div class="container">
            <h2>Overview</h2>
            <p>Welcome to Judy Pharmacy, A place that makes you feel better. Even if you’re not sick in the first place.</p>
            <p>Established in 2016 with a modest 30 m2 space, Judy Pharmacy expanded to become one of the ISO certified pharmacies in Lebanon.</p>

            <p>Judy Mawlawi, PharmD, always believed that healthcare could offer an even more personalized experience and wanted to provide each visitor an exceptional level of care and attention. She trusts that a pharmacy should be “a place that makes you feel better, even if you’re not sick at the first place.”
            </p>
            <p>Navigating the academic journey as a college student in the pharmaceutical department isn’t a walk in the park. Juggling multiple assignments can be overwhelming. In these moments, seeking assistance from a ghostwriter masterarbeit can offer valuable support, ensuring timely submissions without compromising learning. Remember, while external help can aid, personal dedication and understanding are integral. Stay focused, manage time wisely, and seek guidance when needed to thrive in this challenging but rewarding academic path.
            </p>
            <p>At Judy Pharmacy, we do more than fulfilling prescriptions; we help our visitors achieving their health goals. The products we provide are offered together with clear guidance and professional advice. Our highly trained and knowledgeable pharmacists ensure that our visitors receive the highest level of quality service in a safe and friendly environment.
            </p>
            <p>Taking care of your wellbeing for the past 7 years, Judy Pharmacy is your reliable pharmacy offering great-value health products, without ever compromising quality.</p>
        </div>
    </section>


    <!-- Photo Section with Counters -->
    <section class="photo-section">
        <img src="images\static\backgrounds\about.jpg" alt="Background Photo">
        <div class="counter-overlay">
            <div class="counter-item">
                <h2><span class="plus-sign">+</span><span id="yearsExperience">0</span></h2>
                <p>Years of Experience</p>
            </div>
            <div class="counter-item">
                <h2><span class="plus-sign">+</span><span id="followers">0</span></h2>
                <p>Followers</p>
            </div>
            <div class="counter-item">
                <h2><span class="plus-sign">+</span><span id="happyClients">0</span></h2>
                <p>Happy Clients</p>
            </div>
        </div>
    </section>

    <section class="about-overview">
        <div class="overview-container">
            <div class="overview-services">
                <div class="overview-card">
                    <h2>Our Vision</h2>
                    <p>To become the most trusted and accessible pharmacy, providing exceptional care and innovative solutions to enhance health and well-being.</p>
                </div>
                <div class="overview-card">
                    <h2>Our Mission</h2>
                    <p>To deliver high-quality pharmaceutical products and personalized services with a focus on improving lives through reliable healthcare solutions.</p>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer Section -->
    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <h1>Joudi Pharmacy</h1>
            </div>

            <nav class="footer-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php" class="active">About Us</a></li>
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
        // Counter Functionality
        const counters = [{
                id: "yearsExperience",
                target: 7
            },
            {
                id: "followers",
                target: 10000
            },
            {
                id: "happyClients",
                target: 25000
            }
        ];

        function animateCounter(counterElement, target) {
            let count = 0;
            const step = Math.ceil(target / 150);
            const interval = setInterval(() => {
                count += step;
                if (count >= target) {
                    counterElement.textContent = target;
                    clearInterval(interval);
                } else {
                    counterElement.textContent = count;
                }
            }, 20);
        }

        function startCounters() {
            counters.forEach(counter => {
                const element = document.getElementById(counter.id);
                animateCounter(element, counter.target);
            });
        }

        // Trigger animation on scroll
        let started = false;
        window.addEventListener("scroll", () => {
            const section = document.querySelector(".photo-section");
            const sectionTop = section.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            if (sectionTop < windowHeight && !started) {
                started = true;
                startCounters();
            }
        });
    </script>

</body>

</html>