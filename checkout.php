<?php
session_start();
include("includes/db.php");

// Calculate total from session cart
$total = 0;
$delivery_fee = 50;
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
                'name' => $result['name'],
                'quantity' => $quantity,
                'price' => $result['price'],
                'total' => $item_total
            ];
        }
    }
}

$grand_total = $total + $delivery_fee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gitugi Checkout</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #121212;
            margin: 0;
            padding: 0;
            color: #f1f1f1;
        }

        .checkout-container {
            max-width: 500px;
            margin: 40px auto;
            background: #1e1e1e;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.6);
        }

        h2 {
            text-align: center;
            color: #00e676;
        }

        label {
            display: block;
            margin-top: 20px;
            font-weight: 600;
            color: #cccccc;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border-radius: 6px;
            border: 1px solid #444;
            background-color: #2c2c2c;
            color: #f1f1f1;
        }

        .btn {
            background: #00c853;
            color: white;
            padding: 14px;
            border: none;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            margin-top: 25px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #00b74a;
        }

        .order-summary {
            background: #2a2a2a;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 5px solid #00e676;
        }

        .order-summary h4 {
            margin-bottom: 10px;
            color: #00e676;
        }

        .order-summary p, .order-summary strong {
            color: #f1f1f1;
        }

        hr {
            border: none;
            border-top: 1px solid #444;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="checkout-container">
    <h2>Checkout</h2>

    <div class="order-summary">
        <h4>Order Summary</h4>
        <?php if (!empty($order_items)): ?>
            <?php foreach ($order_items as $item): ?>
                <p><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?> - KES <?= number_format($item['total']) ?></p>
            <?php endforeach; ?>
            <p>Delivery Fee - KES <?= number_format($delivery_fee) ?></p>
            <hr>
            <strong>Total: KES <?= number_format($grand_total) ?></strong>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($order_items)): ?>
        <form method="post" action="stk_push.php">
            <label for="phone">M-Pesa Phone Number</label>
            <input type="text" name="phone" id="phone" placeholder="e.g. 07XXXXXXXX" required>

            <input type="hidden" name="amount" value="<?= $grand_total ?>">

            <button type="submit" class="btn">Pay with M-Pesa</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
