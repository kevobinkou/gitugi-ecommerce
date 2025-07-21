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
    <title>Admin Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?> 👋</h2>

    <ul>
        <li><a href="view_orders.php">📦 View Orders</a></li>
        <li><a href="manage_products.php">🛒 Manage Products</a></li>
        <li><a href="admin_logout.php">🚪 Logout</a></li>
    </ul>

    <!-- You can customize this dashboard with statistics or links -->
</body>
</html>
