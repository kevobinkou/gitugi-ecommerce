<?php
session_start();
include("includes/db.php");

// Handle item removal
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    unset($_SESSION['cart'][$remove_id]);
    header("Location: cart.php");
    exit();
}

// Handle cart clearing
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart - Gitugi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #121212;
            color: #f1f1f1;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: #1e1e1e;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        }
        h2 {
            text-align: center;
            color: #00ff88;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            color: #ddd;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        .btn {
            background: #00c853;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn:hover {
            background: #00b248;
        }
        .danger {
            background: #e53935;
        }
        .danger:hover {
            background: #d32f2f;
        }
        .checkout-link, .clear-cart {
            margin-top: 20px;
            text-align: right;
        }
        .checkout-link a, .clear-cart a {
            text-decoration: none;
            color: #00e676;
        }
        .action-links a {
            text-decoration: none;
            color: #f44336;
            font-weight: bold;
        }
        .action-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Shopping Cart</h2>

    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Price (KES)</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
            <?php
            $total = 0;
            foreach ($_SESSION['cart'] as $product_id => $quantity):
                // Fetch product info from DB
                $result = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id LIMIT 1");
                $product = mysqli_fetch_assoc($result);

                if (!$product) continue;

                $subtotal = $product['price'] * $quantity;
                $total += $subtotal;
            ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= $product['price'] ?></td>
                    <td><?= $quantity ?></td>
                    <td><?= $subtotal ?></td>
                    <td class="action-links">
                        <a href="cart.php?remove=<?= $product_id ?>">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3 style="text-align:right; margin-top: 20px;">Grand Total: KES <?= $total ?></h3>

        <div class="checkout-link">
            <a href="checkout.php" class="btn">Proceed to Checkout</a>
        </div>

        <div class="clear-cart">
            <a href="cart.php?clear=true" class="btn danger">Clear Cart</a>
        </div>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>

</body>
</html>
