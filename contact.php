<?php
// Include database connection
include('includes/connection.php'); // Adjust the file name/path if necessary

// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

// Fetch the logged-in user ID
$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_message = trim($_POST['customer_message']);

    if (empty($customer_message)) {
        $error_message = "Message cannot be empty.";
    } else {
        // Insert the message into the contact_message table
        $sql = "INSERT INTO contact_message (customer_id, customer_message) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $customer_message);

        if ($stmt->execute()) {
            $success_message = "Your message has been sent successfully!";
        } else {
            $error_message = "Failed to send your message. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #3498db;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            margin-top: 15px;
        }

        button:hover {
            background-color: #2980b9;
        }

        .success {
            color: green;
            margin-bottom: 15px;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        .back-btn {
            display: block;
            width: 96.5%;
            padding: 10px;
            background-color: #3498db;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
            }

            h2 {
                font-size: 1.5rem;
            }

            button,
            .back-btn {
                font-size: 14px;
                padding: 8px;
            }
        }

        @media (max-width: 480px) {
            .container {
                margin: 10px;
                padding: 15px;
            }

            h2 {
                font-size: 1.2rem;
            }

            button,
            .back-btn {
                font-size: 12px;
                padding: 6px;
            }

            textarea {
                height: 80px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Contact Us</h2>

        <?php if (!empty($success_message)): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="" method="post">
            <label for="customer_message">Your Message:</label>
            <textarea name="customer_message" id="customer_message" required></textarea>
            <button type="submit">Send Message</button>
        </form>

        <!-- Back to Home button -->
        <a href="index.php" class="back-btn">Back to Home</a>
    </div>
</body>

</html>