<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Search
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM products WHERE name LIKE ?";
$stmt = $conn->prepare($query);
$like = "%$search%";
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Products</title>
    <style>
        body { font-family: 'Segoe UI'; background: #121212; color: white; padding: 30px; }
        table { width: 100%; background: #1f1f1f; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #333; }
        th { background: #00c853; color: black; }
        a { color: #00e676; }
        input[type="text"] {
            padding: 8px; width: 300px; background: #2a2a2a; color: #fff;
            border: none; border-radius: 5px;
        }
    </style>
</head>
<body>
    <h2>Manage Products</h2>

    <form method="get">
        <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <table>
        <tr>
            <th>#</th><th>Name</th><th>Price</th><th>Stock</th><th>Category</th><th>Action</th>
        </tr>
        <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>KES <?= number_format($row['price'], 2) ?></td>
                <td><?= $row['stock'] ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td>
                    <a href="edit_product.php?id=<?= $row['id'] ?>">Edit</a> | 
                    <a href="delete_product.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <br><a href="add_product.php">➕ Add New Product</a> |
    <a href="admin_dashboard.php">⬅️ Back to Dashboard</a>
</body>
</html>
