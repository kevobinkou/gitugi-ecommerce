<?php
session_start();
include("includes/db.php");

$cart = $_SESSION['cart'] ?? [];
$total = 0;
$vatRate = 0.16;
$deliveryFee = 50; // Optional: Remove if not needed
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart - Gitugi Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #121212;
            color: #f1f1f1;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #00e676;
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
            color: #fff;
        }

        .total-row {
            font-weight: bold;
            text-align: right;
        }

        .checkout-btn {
            display: block;
            width: 90%;
            margin: 20px auto;
            padding: 12px;
            background-color: #00c853;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .checkout-btn:hover {
            background-color: #00b248;
        }

        @media (max-width: 600px) {
            table, th, td {
                font-size: 14px;
            }

            .checkout-btn {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<h1>Your Shopping Cart</h1>

<?php if (empty($cart)): ?>
    <p style="text-align: center;">🛒 Your cart is empty.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Product</th>
            <th>Price (KES)</th>
            <th>Qty</th>
            <th>Subtotal (KES)</th>
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

        $vatAmount = $total * $vatRate;
        $grandTotal = $total + $vatAmount + $deliveryFee;
        ?>

        <tr class="total-row">
            <td colspan="3">Subtotal</td>
            <td><?= number_format($total, 2) ?></td>
        </tr>
        <tr class="total-row">
            <td colspan="3">VAT (16%)</td>
            <td><?= number_format($vatAmount, 2) ?></td>
        </tr>
        <tr class="total-row">
            <td colspan="3">Delivery Fee</td>
            <td><?= number_format($deliveryFee, 2) ?></td>
        </tr>
        <tr class="total-row">
            <td colspan="3"><strong>Grand Total</strong></td>
            <td><strong><?= number_format($grandTotal, 2) ?></strong></td>
        </tr>
    </table>

    <a href="checkout.php" class="checkout-btn">🚀 Proceed to Checkout</a>
<?php endif; ?>

</body>
</html>
