<?php
session_start();
include('../includes/connection.php');

// Ensure the user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the users table
$userSql = "SELECT name, email, phone_number, image_path FROM users WHERE id = ?";
$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows > 0) {
    $userDetails = $userResult->fetch_assoc();
} else {
    echo "User not found!";
    exit();
}

// Fetch address details from the addresses table
$addressSql = "SELECT street, city, state, country FROM addresses WHERE user_id = ?";
$addressStmt = $conn->prepare($addressSql);
$addressStmt->bind_param("i", $user_id);
$addressStmt->execute();
$addressResult = $addressStmt->get_result();

if ($addressResult->num_rows > 0) {
    $addressDetails = $addressResult->fetch_assoc();
    $addressExists = true;
} else {
    // No address found
    $addressDetails = [
        'street' => null,
        'city' => null,
        'state' => null,
        'country' => null,
    ];
    $addressExists = false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and update user details
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $country = trim($_POST['country']);
    
    // Image upload logic
    $imagePath = $userDetails['image_path'];  // Default to current image

    if (isset($_FILES['user_photo']) && $_FILES['user_photo']['error'] == 0) {
        $targetDir = "C:/xampp/htdocs/joudi_pharmacy/images/users_uploads/user_photo/";
        $imageName = basename($_FILES['user_photo']['name']);
        $targetFile = $targetDir . $imageName;

        // Check if file is an image
        $imageType = mime_content_type($_FILES['user_photo']['tmp_name']);
        if (strpos($imageType, 'image') === false) {
            $error = "Please upload a valid image file!";
        } else {
            // Move uploaded file to the target directory
            if (move_uploaded_file($_FILES['user_photo']['tmp_name'], $targetFile)) {
                $imagePath = '../images/users_uploads/user_photo/' . $imageName;
            } else {
                $error = "Failed to upload image!";
            }
        }
    }

    // Validate inputs (basic validation)
    if (empty($name) || empty($email) || empty($phone_number) || empty($street) || empty($city) || empty($state) || empty($country)) {
        $error = "All fields are required!";
    } else {
        // Update user details
        $updateUserSql = "UPDATE users SET name = ?, email = ?, phone_number = ?, image_path = ? WHERE id = ?";
        $stmt = $conn->prepare($updateUserSql);
        $stmt->bind_param("ssssi", $name, $email, $phone_number, $imagePath, $user_id);
        $stmt->execute();

        // Insert or update address details
        if ($addressExists) {
            $updateAddressSql = "UPDATE addresses SET street = ?, city = ?, state = ?, country = ? WHERE user_id = ?";
            $stmt = $conn->prepare($updateAddressSql);
            $stmt->bind_param("ssssi", $street, $city, $state, $country, $user_id);
        } else {
            $insertAddressSql = "INSERT INTO addresses (street, city, state, country, user_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertAddressSql);
            $stmt->bind_param("ssssi", $street, $city, $state, $country, $user_id);
        }
        $stmt->execute();

        header('location: profile.php');
        exit();
    }
}

$addressStmt->close();
$userStmt->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Your existing styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }

        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .form-container h2 {
            color: #333;
        }

        .form-container input,
        .form-container textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        .form-container input[type="submit"] {
            background-color: #5b9bd5;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.1em;
        }

        .form-container input[type="submit"]:hover {
            background-color: #4a8fc4;
        }

        .error {
            color: red;
            font-size: 1.1em;
        }

        .success {
            color: green;
            font-size: 1.1em;
        }

        .back-to-home {
            text-align: center;
            display: block;
            margin-top: 20px;
            font-size: 1.1em;
            color: #5b9bd5;
            text-decoration: none;
        }

        .back-to-home:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Update Form -->
        <div class="form-container">
            <h2>Update Your Details</h2>

            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php elseif (isset($success)): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>

            <form method="POST" action="update_profile.php" enctype="multipart/form-data">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo $userDetails['name']; ?>" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $userDetails['email']; ?>" required>

                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" value="<?php echo $userDetails['phone_number']; ?>" required>

                <label for="street">Street</label>
                <input type="text" id="street" name="street" value="<?php echo $addressDetails['street']; ?>" required>

                <label for="city">City</label>
                <input type="text" id="city" name="city" value="<?php echo $addressDetails['city']; ?>" required>

                <label for="state">State</label>
                <input type="text" id="state" name="state" value="<?php echo $addressDetails['state']; ?>" required>

                <label for="country">Country</label>
                <input type="text" id="country" name="country" value="<?php echo $addressDetails['country']; ?>" required>

                <label for="user_photo">Profile Photo</label>
                <input type="file" id="user_photo" name="user_photo" accept="image/*">

                <input type="submit" value="Update Details">
            </form>
            <a href="profile.php" class="back-to-home">Back to Profile</a>
        </div>

        
        
    </div>

</body>

</html>
