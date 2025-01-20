<?php
session_start();
include('../../includes/connection.php');

// Ensure the user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user and address details from the database
$user_sql = "SELECT phone_number FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_details = $user_result->fetch_assoc();

$address_sql = "SELECT street, city, state, country FROM addresses WHERE user_id = ?";
$address_stmt = $conn->prepare($address_sql);
$address_stmt->bind_param("i", $user_id);
$address_stmt->execute();
$address_result = $address_stmt->get_result();
$address_details = $address_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['phone_number'], $_POST['street'], $_POST['city'], $_POST['state'], $_POST['country'])) {
        $phone_number = $_POST['phone_number'];
        $street = $_POST['street'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $country = $_POST['country'];

        // Update phone number in users table
        $update_user_sql = "UPDATE users SET phone_number = ? WHERE id = ?";
        $update_user_stmt = $conn->prepare($update_user_sql);
        $update_user_stmt->bind_param("si", $phone_number, $user_id);
        $update_user_stmt->execute();
        $update_user_stmt->close();

        // Update address in user_addresses table
        $update_address_sql = "UPDATE addresses SET street = ?, city = ?, state = ?, country = ? WHERE user_id = ?";
        $update_address_stmt = $conn->prepare($update_address_sql);
        $update_address_stmt->bind_param("ssssi", $street, $city, $state, $country, $user_id);
        $update_address_stmt->execute();
        $update_address_stmt->close();

        // Redirect to checkout page with a success message
        header("Location: checkout.php?message=Details updated successfully");
        exit();
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User Details</title>
</head>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f0f8ff;
        color: #333;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 80%;
        margin: 0 auto;
        padding: 20px;
        background-color: #f2f9ff;
        /* Light baby blue */
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    h1 {
        color: #007bff;
        /* Baby blue color */
        text-align: center;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    label {
        font-weight: bold;
        color: #333;
    }

    input {
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    button.update-button {
        padding: 10px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }


    button.update-button:hover {
        background-color: #0056b3;
    }

    a.back-to-cart-button {
        margin-top: 15px;
        padding: 8px 16px;
        background: #007acc;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }

    a.back-to-cart-button:hover {
        background: #005a99;
    }
</style>

<body>

    <div class="container">
        <h1>Update Your Details</h1>

        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form method="POST" action="update_user_details.php">
            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user_details['phone_number']); ?>" required>

            <label for="street">Street:</label>
            <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($address_details['street']); ?>" required>

            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($address_details['city']); ?>" required>

            <label for="state">State:</label>
            <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($address_details['state']); ?>" required>

            <label for="country">Country:</label>
            <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($address_details['country']); ?>" required>

            <button type="submit" class="update-button">Update Details</button>
            <div class="back-to-cart-button">
                <a href="cart.php" class="back-to-cart-button">Back to Cart</a>
            </div>
        </form>


    </div>

</body>

</html>