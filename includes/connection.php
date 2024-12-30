<?php
// Database connection details
$host = "localhost";
$username = "root";
$password = "";
$database = "joudi_pharmacy";

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
