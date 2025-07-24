<?php
session_start();
include("includes/db.php");

if (!isset($_GET['order_id'])) {
    die("❌ Invalid access. No order ID provided.");
}

$order_id = intval($_GET['order_id']);

// Fetch order and items
$stmt = $conn->prepare("SELECT o.id, o.delivery_fee, o.created_at, o.status, i.product_id, i.quantity, i.price, p.name 
    FROM orders o
    JOIN order_items i ON o.id = i.order_id
    JOIN products p ON i.product_id = p.id
    WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ No receipt found for this order.");
}

$items = [];
$delivery_fee = 0;
$date = "";
$status = "";

while ($row = $result->fetch_assoc()) {
    $delivery_fee = $row['delivery_fee'];
    $date = $row['created_at'];
    $status = $row['status'];

    $items[] = [
        "name" => $row['name'],
        "quantity" => $row['quantity'],
        "price" => $row['price']
    ];
}

// Calculate totals
$total = 0;
foreach ($items as $item) {
    $total += $item["quantity"] * $item["price"];
}
$grand_total = $total + $delivery_fee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - Gitugi E-commerce</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #121212;
            color: #f1f1f1;
            margin: 0;
            padding: 20px;
        }

        .receipt-container {
            max-width: 600px;
            margin: auto;
            background: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 255, 128, 0.2);
        }

        h2 {
            color: #00e676;
            text-align: center;
            margin-bottom: 20px;
        }

        .receipt-section {
            margin-bottom: 20px;
        }

        .receipt-section p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #333;
        }

        th {
            background-color: #00c853;
            color: #fff;
        }

        .total-row {
            font-weight: bold;
        }

        .btn {
            display: block;
            margin: 20px auto 0;
            padding: 12px 24px;
            background: #00e676;
            color: #121212;
            border: none;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
        }

        .btn:hover {
            background: #00c853;
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <h2>Payment Receipt</h2>

    <div class="receipt-section">
        <p><strong>Order ID:</strong> #<?= htmlspecialchars($order_id) ?></p>
        <p><strong>Status:</strong> <?= ucfirst($status) ?></p>
        <p><strong>Date:</strong> <?= date("Y-m-d H:i", strtotime($date)) ?></p>
    </div>

    <div class="receipt-section">
        <h4>Items</h4>
        <table>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item["name"]) ?></td>
                    <td><?= $item["quantity"] ?></td>
                    <td>KES <?= number_format($item["price"] * $item["quantity"]) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td>Delivery</td>
                <td> - </td>
                <td>KES <?= number_format($delivery_fee) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="2">Total</td>
                <td>KES <?= number_format($grand_total) ?></td>
            </tr>
        </table>
    </div>

    <a href="index.php" class="btn">Back to Shop</a>
</div>

</body>
</html>
