<?php
session_start();
include '../../includes/connection.php';

// Check if user_id session exists and role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin role required.');
}

$prescriptionId = $_GET['id']; // Get the prescription ID from the URL

// Fetch prescription data
$sqlPrescription = "SELECT p.*, u.name AS customer_name 
                    FROM prescriptions p 
                    INNER JOIN users u ON p.customer_id = u.id 
                    WHERE p.id = ?";
$stmtPrescription = $conn->prepare($sqlPrescription);
$stmtPrescription->bind_param("i", $prescriptionId);
$stmtPrescription->execute();
$resultPrescription = $stmtPrescription->get_result();
$prescription = $resultPrescription->fetch_assoc();

if (!$prescription) {
    header("Location: view_all_prescriptions.php");
    exit();
}

// Fetch pharmacist list
$sqlPharmacists = "SELECT id, name FROM users WHERE role = 'pharmacist'";
$resultPharmacists = $conn->query($sqlPharmacists);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $status = $conn->real_escape_string($_POST['status']);
    $pharmacistId = $conn->real_escape_string($_POST['pharmacist_id']);
    $quantities = $_POST['quantities'];

    // Start transaction
    $conn->begin_transaction();
    try {
        // Update prescription
        $sqlUpdatePrescription = "UPDATE prescriptions SET status = ?, pharmacist_id = ? WHERE id = ?";
        $stmtUpdatePrescription = $conn->prepare($sqlUpdatePrescription);
        $stmtUpdatePrescription->bind_param("sii", $status, $pharmacistId, $prescriptionId);
        $stmtUpdatePrescription->execute();

        // If approved, add items to cart
        if ($status === 'approved') {
            foreach ($quantities as $productId => $quantity) {
                $sqlAddToCart = "INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)";
                $stmtAddToCart = $conn->prepare($sqlAddToCart);
                $stmtAddToCart->bind_param("iii", $prescription['customer_id'], $productId, $quantity);
                $stmtAddToCart->execute();
            }
        }

        $conn->commit();
        header("Location: view_all_prescriptions.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Failed to update prescription. Please try again.";
    }
}

// Fetch prescription items
$sqlItems = "SELECT pi.product_id, pr.name, pr.price 
             FROM prescription_items pi
             INNER JOIN products pr ON pi.product_id = pr.id
             WHERE pi.prescription_id = ?";
$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param("i", $prescriptionId);
$stmtItems->execute();
$resultItems = $stmtItems->get_result();
$items = $resultItems->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Prescription</title>
    <style>
        /* Add styles similar to the provided example */
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
        select {
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
    </style>
</head>

<body>
    <div class="container">
    <div class="sidebar">
            <h2>Admin Panel</h2>
            <a href="../../index.php">Home</a>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../users/view_all_users.php">Users</a>
            <a href="../products/view_all_products.php">Products</a>
            <a href="../orders/view_all_orders.php">Orders</a>
            <a href="view_all_prescriptions.php">Prescriptions</a>
            <a href="../contacts/view_all_contacts.php">Contacts</a>
            <a href="../../includes/logout.php">Log out</a>
        </div>
        <div class="content">
            <h1>Edit Prescription</h1>

            <?php if (isset($error)) : ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="pending" <?php echo $prescription['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $prescription['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $prescription['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pharmacist_id">Assign Pharmacist:</label>
                    <select id="pharmacist_id" name="pharmacist_id" required>
                        <option value="">Select Pharmacist</option>
                        <?php while ($pharmacist = $resultPharmacists->fetch_assoc()) : ?>
                            <option value="<?php echo $pharmacist['id']; ?>" <?php echo $prescription['pharmacist_id'] == $pharmacist['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pharmacist['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <h2>Prescription Items</h2>
                <?php foreach ($items as $item) : ?>
                    <div class="form-group">
                        <label for="quantities[<?php echo $item['product_id']; ?>]">
                            <?php echo htmlspecialchars($item['name']); ?> (Price: <?php echo $item['price']; ?>)
                        </label>
                        <input type="number" name="quantities[<?php echo $item['product_id']; ?>]" id="quantities[<?php echo $item['product_id']; ?>]" value="1" min="1" required>
                    </div>
                <?php endforeach; ?>

                <button type="submit" name="submit">Update Prescription</button>
            </form>
        </div>
    </div>
</body>

</html>
