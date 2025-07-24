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

define('VAT_RATE', 0.16);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart - Gitugi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* ... same CSS styles ... */
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
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $product_id => $quantity):
                $result = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id LIMIT 1");
                $product = mysqli_fetch_assoc($result);

                if (!$product) continue;

                $line_total = $product['price'] * $quantity;
                $subtotal += $line_total;
            ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= number_format($product['price'], 2) ?></td>
                    <td><?= $quantity ?></td>
                    <td><?= number_format($line_total, 2) ?></td>
                    <td class="action-links">
                        <a href="cart.php?remove=<?= $product_id ?>">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php
            $vat = $subtotal * VAT_RATE;
            $grand_total = $subtotal + $vat;
        ?>

        <div style="margin-top: 30px; text-align: right;">
            <p>Subtotal (Before VAT): <strong>KES <?= number_format($subtotal, 2) ?></strong></p>
            <p>VAT (16%): <strong>KES <?= number_format($vat, 2) ?></strong></p>
            <p style="font-size: 1.2em;">Total (Incl. VAT): <strong>KES <?= number_format($grand_total, 2) ?></strong></p>
        </div>

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
