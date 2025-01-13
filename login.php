<?php
session_start();
include "includes/connection.php"; // Ensure this file connects to your database.

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verify the password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'customer') {
                header("Location: index.php");
            } elseif ($user['role'] === 'pharmacist') {
                header("Location: pharmacist/dashboard.php");
            } elseif ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid email or password.');</script>";
        }
    } else {
        echo "<script>alert('User not found.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Login</title>
  <style>
    /* Base Styles */
    body {
      font-family: "Roboto", sans-serif;
      margin: 0;
      padding: 0;
      background-image: url("images/static/backgrounds/formesBackground.jpg");
      background-size: cover;
      color: #ffffff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    /* Card Container */
    .card {
      background: rgba(255, 255, 255, 0.15); /* Semi-transparent white */
      backdrop-filter: blur(12px); /* Glass effect */
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
      padding: 2rem;
      width: 90%;
      max-width: 400px;
      text-align: center;
    }

    /* Header */
    .card h2 {
      margin-bottom: 1.5rem;
      font-size: 1.8rem;
      color: #ffffff;
    }

    /* Form Styles */
    .card form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .card input {
      background: rgba(255, 255, 255, 0.8); /* Transparent input fields */
      border: none;
      border-radius: 8px;
      padding: 0.8rem;
      font-size: 1rem;
      color:rgb(92, 90, 90);
      outline: none;
    }

    .card input::placeholder {
      color:rgba(173, 171, 171, 0.65);
    }

    .card input:focus {
      border: 1px solid #4fc3f7; /* Light blue border for focus */
      background: rgba(133, 131, 131, 0.1);
    }

    /* Button Styles */
    .card button {
      background-color: #0077cc; /* Blue button */
      color: #ffffff;
      border: none;
      border-radius: 8px;
      padding: 0.8rem;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.2s;
    }

    .card button:hover {
      background-color: #005fa3;
      transform: scale(1.05);
    }

    /* Signup Link */
    .card p {
      margin-top: 1rem;
      font-size: 0.9rem;
      color: rgba(133, 131, 131, 0.8);
    }

    .card a {
      color: #4fc3f7; /* Light blue */
      text-decoration: none;
    }

    .card a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>Login</h2>
    <form action="" method="POST">
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" name="login">Login</button>
    </form>
    <p>Customer you don't have an account? <a href="customer/signup.php">Sign Up !</a></p>
  </div>
</body>
</html>
