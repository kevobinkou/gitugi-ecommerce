<?php
session_start();
include("includes/db.php");

if (!isset($_GET['checkout_id'])) {
    die("❌ No Checkout ID provided.");
}

$checkout_id = $_GET['checkout_id'];

// Query mpesa_payments
$stmt = $conn->prepare("SELECT * FROM mpesa_payments WHERE CheckoutRequestID = ?");
$stmt->bind_param("s", $checkout_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ No payment found for this request.");
}

$payment = $result->fetch_assoc();

// Check payment result
$resultCode = $payment['ResultCode'];
$order_id = $payment['order_id'];
$status_message = "";

if ($resultCode === null) {
    $status_message = "⌛ Awaiting payment confirmation from M-Pesa...";
    $refresh = true;
} elseif ($resultCode == 0) {
    $status_message = "✅ Payment successful! Redirecting to receipt...";
    header("refresh:3; url=receipt.php?order_id=$order_id");
    $refresh = false;
} else {
    $status_message = "❌ Payment failed: " . htmlspecialchars($payment['ResultDesc']);
    $refresh = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Status - Gitugi E-commerce</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (isset($refresh) && $refresh): ?>
        <meta http-equiv="refresh" content="5">
    <?php endif; ?>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #121212;
            color: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .status-box {
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 0 12px rgba(0, 255, 128, 0.2);
            max-width: 400px;
        }

        .status-box h2 {
            color: #00e676;
        }

        .btn {
            margin-top: 20px;
            padding: 10px 20px;
            background: #00c853;
            color: #121212;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .btn:hover {
            background: #00b248;
        }
    </style>
</head>
<body>

<div class="status-box">
    <h2>Payment Status</h2>
    <p><?= $status_message ?></p>

    <?php if (!$refresh && $resultCode != 0): ?>
        <a href="checkout.php" class="btn">Try Again</a>
    <?php endif; ?>
</div>

</body>
</html>
