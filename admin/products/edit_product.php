<?php
session_start();
include '../../includes/connection.php';

// Check if user_id session exists and role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin role required.');
}

$user_id = $_SESSION['user_id'];

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Product not found!";
    exit();
}

$product = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category'];
    $stock = $_POST['stock'];
    $is_prescription_required = isset($_POST['is_prescription_required']) ? 1 : 0;

    // Handle file upload (if a new image is uploaded)
    $imagePath = $product['image_path']; // Keep the old image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../../images/products_uploads/";
        $fileName = basename($_FILES['image']['name']);
        $targetFilePath = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                $imagePath = "images/products_uploads/" . $fileName;
            } else {
                echo "Error uploading image.";
                exit();
            }
        } else {
            echo "Invalid file type.";
            exit();
        }
    }

    // Update product in the database
    $sqlUpdate = "UPDATE products 
                  SET name = ?, description = ?, price = ?, category_id = ?, stock = ?, is_prescription_required = ?, image_path = ? 
                  WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ssdiiisi", $name, $description, $price, $category_id, $stock, $is_prescription_required, $imagePath, $product_id);

    if ($stmtUpdate->execute()) {
        header("Location: view_all_products.php?message=Product+updated+successfully");
        exit();
    } else {
        echo "Error: " . $stmtUpdate->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            background-color: #2c3e50;
            color: #ecf0f1;
            width: 198px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            margin-bottom: 25px;
            text-align: center;
            font-size: 1.5rem;
        }

        .sidebar a {
            text-decoration: none;
            color: #ecf0f1;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        .content {
            flex: 1;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #2980b9;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group img {
            max-width: 100px;
            margin-top: 10px;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .radio-group label {
            font-weight: normal;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../users/view_all_users.php">Users</a>
            <a href="view_all_products.php">Products</a>
            <a href="../../orders/view_all.php">Orders</a>
            <a href="../../prescriptions/view_all.php">Prescriptions</a>
            <a href="../../settings.php">Settings</a>
            <a href="../../includes/logout.php">Log out</a>
        </div>

        <!-- Content -->
        <div class="content">
            <h1>Update Product</h1>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Product Name:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars($product['price']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <?php
                        $categories = $conn->query("SELECT * FROM categories");
                        while ($category = $categories->fetch_assoc()) {
                            $selected = $category['id'] == $product['category_id'] ? 'selected' : '';
                            echo "<option value='{$category['id']}' $selected>{$category['name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="stock">Stock:</label>
                    <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($product['stock']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Prescription Required:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="is_prescription_required" value="1" <?= $product['is_prescription_required'] ? 'checked' : '' ?>> Yes
                        </label>
                        <label>
                            <input type="radio" name="is_prescription_required" value="0" <?= !$product['is_prescription_required'] ? 'checked' : '' ?>> No
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Product Image:</label>
                    <input type="file" id="image" name="image">
                    <p>Current Image:</p>
                    <img src="../../<?= $product['image_path'] ?>" alt="Product Image">
                </div>

                <button type="submit">Update Product</button>
            </form>
        </div>
    </div>
</body>

</html>