<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Gitugi E-commerce</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f8;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .dashboard {
            max-width: 800px;
            margin: 50px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
        }
        .nav {
            display: flex;
            justify-content: space-around;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        .nav a {
            text-decoration: none;
            background: #008cba;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            margin: 10px;
            transition: background 0.3s ease;
            display: inline-block;
        }
        .nav a:hover {
            background: #005f7f;
        }
        .msg {
            margin-top: 20px;
            font-weight: bold;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?> 👋</h2>

        <div class="msg">
            <?php
            if (isset($_SESSION['success'])) {
                echo "<p class='success'>" . $_SESSION['success'] . "</p>";
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo "<p class='error'>" . $_SESSION['error'] . "</p>";
                unset($_SESSION['error']);
            }
            ?>
        </div>

        <div class="nav">
            <a href="view_orders.php">📦 View Orders</a>
            <a href="manage_products.php">🛒 Manage Products</a>
            <a href="admin_logout.php">🚪 Logout</a>
        </div>
    </div>
</body>
</html>
