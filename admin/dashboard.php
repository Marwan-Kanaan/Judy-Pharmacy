<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <style>
    /* Reset and Base Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    /* Sidebar */
    .sidebar {
      background-color: #2c3e50;
      color: #ecf0f1;
      width: 250px;
      padding: 20px;
      display: flex;
      flex-direction: column;
    }
    .sidebar h2 {
      margin-bottom: 20px;
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

    /* Top Navbar */
    .navbar {
      background-color: #34495e;
      color: #ecf0f1;
      height: 60px;
      width: calc(100% - 250px);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
    }
    .navbar .search-box {
      background-color: #2c3e50;
      border: none;
      padding: 10px;
      border-radius: 5px;
      color: #ecf0f1;
    }
    .navbar .profile {
      display: flex;
      align-items: center;
    }
    .navbar .profile img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
    }

    /* Content Area */
    .content {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
    }
    .content h1 {
      margin-bottom: 20px;
    }
    .content .stats {
      display: flex;
      gap: 20px;
    }
    .stats .card {
      background-color: #ecf0f1;
      padding: 20px;
      border-radius: 10px;
      flex: 1;
      text-align: center;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .stats .card h3 {
      font-size: 1.2rem;
      margin-bottom: 10px;
    }
    .stats .card p {
      font-size: 2rem;
      color: #2c3e50;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="#">Dashboard</a>
    <a href="#">Users</a>
    <a href="#">Products</a>
    <a href="#">Orders</a>
    <a href="#">Prescriptions</a>
    <a href="#">Settings</a>
  </div>

  <!-- Main Content -->
  <div class="main">
    <!-- Navbar -->
    <div class="navbar">
      <input type="text" class="search-box" placeholder="Search...">
      <div class="profile">
        <img src="https://via.placeholder.com/40" alt="Profile">
        <span>Admin</span>
      </div>
    </div>

    <!-- Content Area -->
    <div class="content">
      <h1>Welcome to the Admin Dashboard</h1>
      <div class="stats">
        <div class="card">
          <h3>Total Users</h3>
          <p>120</p>
        </div>
        <div class="card">
          <h3>Total Products</h3>
          <p>450</p>
        </div>
        <div class="card">
          <h3>Pending Orders</h3>
          <p>35</p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
