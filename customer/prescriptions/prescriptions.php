<?php
// Include database connection
include('../../includes/connection.php');

// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_ids = isset($_POST['product_ids']) ? $_POST['product_ids'] : [];
    $errors = [];

    // Validate that at least one product is selected
    if (empty($product_ids)) {
        $errors[] = "Please select at least one product.";
    }

   // Handle prescription image upload
$prescription_image_name = null;
if (isset($_FILES['prescription_image']) && $_FILES['prescription_image']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../../images/users_uploads/user_prescriptions/";
    $file_name = time() . "_" . basename($_FILES['prescription_image']['name']);
    $target_file = $target_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
    if (in_array($file_type, $allowed_types)) {
        if (move_uploaded_file($_FILES['prescription_image']['tmp_name'], $target_file)) {
            $prescription_image_name = $file_name;  // Store only the filename in the database
        } else {
            $errors[] = "Failed to upload prescription image.";
        }
    } else {
        $errors[] = "Only JPG, JPEG, PNG, and PDF files are allowed for prescription.";
    }
} else {
    $errors[] = "Please upload a prescription image.";
}

// Handle ID proof image upload (required)
$id_image_name = null;
if (isset($_FILES['id_image']) && $_FILES['id_image']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../../images/users_uploads/user_id_photo/";
    $file_name = time() . "_" . basename($_FILES['id_image']['name']);
    $target_file = $target_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
    if (in_array($file_type, $allowed_types)) {
        if (move_uploaded_file($_FILES['id_image']['tmp_name'], $target_file)) {
            $id_image_name = $file_name;  // Store only the filename in the database
        } else {
            $errors[] = "Failed to upload ID proof.";
        }
    } else {
        $errors[] = "Only JPG, JPEG, PNG, and PDF files are allowed for ID proof.";
    }
} else {
    $errors[] = "Please upload your ID proof image.";
}


    // Handle data insertion if no errors
    if (empty($errors)) {
        // Insert prescription record with image file names (not full paths)
        $sql = "INSERT INTO prescriptions (customer_id, status, image, customer_id_image) 
                VALUES (?, 'pending', ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $prescription_image_name, $id_image_name);
        
        if ($stmt->execute()) {
            $prescription_id = $stmt->insert_id;
            
            // Insert associated product items into prescription_items table
            foreach ($product_ids as $product_id) {
                $sql = "INSERT INTO prescription_items (prescription_id, product_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $prescription_id, $product_id);
                $stmt->execute();
            }

            $success_message = "Prescription submitted successfully.";
        } else {
            $errors[] = "Failed to submit prescription. Please try again.";
        }
    }
}
?>

<!-- HTML Form for Prescription Submission -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Prescription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 900px;
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
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #3498db;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .success {
            color: green;
            margin-bottom: 15px;
        }
        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .product-item {
            flex: 1 1 calc(25% - 15px);
            min-width: 150px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .product-item input {
            width: auto;
        }
        .back-btn {
            display: inline-block;
            background-color: #e74c3c;
            color: #fff;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .back-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<!-- Prescription Form -->
<div class="container">
    <h2>Submit Your Prescription</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <div class="success">
            <p><?php echo $success_message; ?></p>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label for="product_ids">Select Products:</label>
        <select name="product_ids[]" id="product_ids" multiple required>
            <?php
            $result = $conn->query("SELECT id, name FROM products WHERE is_prescription_required = 1");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="prescription_image">Prescription Image:</label>
        <input type="file" name="prescription_image" id="prescription_image" required>

        <label for="id_image">ID Proof Image:</label>
        <input type="file" name="id_image" id="id_image" required>

        <button type="submit">Submit Prescription</button>
    </form>

    <a href="../../index.php" class="back-btn">Back to Home</a>
</div>

</body>
</html>
