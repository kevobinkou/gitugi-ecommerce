<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

include("includes/db.php");

// ✅ Update order status if requested
if (isset($_GET['complete_order'])) {
    $order_id = intval($_GET['complete_order']);

    $update = $conn->prepare("UPDATE orders SET status = 'Completed' WHERE id = ?");
    $update->bind_param("i", $order_id);
    $update->execute();
}

// ✅ Fetch orders
$sql = "SELECT * FROM orders ORDER BY order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Orders</title>
    <style>
        table {
            width: 95%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            padding: 10px;
            border: 1px solid #999;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        a.button {
            padding: 6px 12px;
            background-color: green;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a.button:hover {
            background-color: darkgreen;
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;">📦 All Orders</h2>
    <table>
        <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td>KES <?= number_format($row['total_price'], 2) ?></td>
                <td><?= $row['status'] ?></td>
                <td><?= $row['order_date'] ?></td>
                <td>
                    <?php if ($row['status'] === 'Pending'): ?>
                        <a class="button" href="view_orders.php?complete_order=<?= $row['id'] ?>" onclick="return confirm('Mark this order as completed?')">Mark as Completed</a>
                    <?php else: ?>
                        ✅
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="10">No orders found.</td></tr>
        <?php endif; ?>
    </table>

    <div style="text-align:center;">
        <a href="ad
