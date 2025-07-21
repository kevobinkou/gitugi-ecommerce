<?php
session_start();
include("includes/db.php");

$cart = $_SESSION['cart'] ?? [];

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <style>
        body {
            background-color: #121212;
            color: #f1f1f1;
            font-family: 'Segoe UI', sans-serif;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #1f1f1f;
        }
        th, td {
            padding: 12px;
            border: 1px solid #333;
            text-align: left;
        }
        th {
            background-color: #292929;
        }
        h1 {
            text-align: center;
            color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Your Shopping Cart</h1>
    <?php if (empty($cart)): ?>
        <p style="text-align: center;">Your cart is empty.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Price (KES)</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
            <?php
            foreach ($cart as $product_id => $quantity) {
                $query = "SELECT * FROM products WHERE id = " . intval($product_id);
                $result = mysqli_query($conn, $query);
                if ($product = mysqli_fetch_assoc($result)) {
                    $subtotal = $product['price'] * $quantity;
                    $total += $subtotal;
                    echo "<tr>
                            <td>" . htmlspecialchars($product['name']) . "</td>
                            <td>" . number_format($product['price'], 2) . "</td>
                            <td>" . $quantity . "</td>
                            <td>" . number_format($subtotal, 2) . "</td>
                          </tr>";
                }
            }
            ?>
            <tr>
                <th colspan="3" style="text-align:right;">Grand Total</th>
                <th><?= number_format($total, 2) ?></th>
            </tr>
        </table>
    <?php endif; ?>
</body>
</html>
