<?php
session_start();
include("includes/db.php");

define("VAT_RATE", 0.16);
$delivery_fee = 50;

$total = 0;
$order_items = [];

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            $item_total = $result['price'] * $quantity;
            $total += $item_total;

            $order_items[] = [
                'id' => $product_id,
                'name' => $result['name'],
                'quantity' => $quantity,
                'price' => $result['price'],
                'total' => $item_total
            ];
        }
    }
}

$vat = $total * VAT_RATE;
$grand_total = $total + $vat + $delivery_fee;

// Save order in DB
$order_id = null;
if (!empty($order_items)) {
    $stmt = $conn->prepare("INSERT INTO orders (subtotal, vat, delivery_fee, total_amount, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("dddd", $total, $vat, $delivery_fee, $grand_total);
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        // Save order items
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($order_items as $item) {
            $item_stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $item_stmt->execute();
        }
    }
}
?>

<!-- HTML part stays almost the same -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gitugi Checkout</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>/* your original CSS stays unchanged */</style>
</head>
<body>

<div class="checkout-container">
    <h2>Checkout</h2>

    <div class="order-summary">
        <h4>Order Summary</h4>
        <?php if (!empty($order_items)): ?>
            <?php foreach ($order_items as $item): ?>
                <p><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?> - KES <?= number_format($item['total'], 2) ?></p>
            <?php endforeach; ?>
            <p>Subtotal - KES <?= number_format($total, 2) ?></p>
            <p>VAT (16%) - KES <?= number_format($vat, 2) ?></p>
            <p>Delivery - KES <?= number_format($delivery_fee, 2) ?></p>
            <hr>
            <strong>Total: KES <?= number_format($grand_total, 2) ?></strong>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($order_items) && $order_id): ?>
        <form method="post" action="stk_push.php">
            <label for="phone">M-Pesa Phone Number</label>
            <input type="text" name="phone" id="phone" placeholder="e.g. 07XXXXXXXX" required>

            <input type="hidden" name="amount" value="<?= $grand_total ?>">
            <input type="hidden" name="order_id" value="<?= $order_id ?>">

            <button type="submit" class="btn">Pay with M-Pesa</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
