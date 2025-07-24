<?php
session_start();
include("includes/db.php");
date_default_timezone_set('Africa/Nairobi');

// ====== CONFIG (move to env/secrets in production) ===========================
$consumerKey    = "QEf8LGMFgtqbhZOVU0p4FOrg1et60i2qhPWY8qYvY8rv8vEP";
$consumerSecret = "bhU2jT2bu7kAn1E8cd8p8g6ZCGCkjcqXoz6WexLggn0zWJGBHhHWiDCDI5KilZ8J";
$shortcode      = "174379";
$passkey        = "YOUR_FULL_SANDBOX_PASSKEY_HERE";
$callbackURL    = "https://gitugi-ecommerce-xxxx.cleverapps.io/callback_url.php"; // <- make sure it matches what you whitelisted
// ============================================================================

// Validate input
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$amount   = isset($_POST['amount'])   ? (float)$_POST['amount']   : 0.0;
$phone    = trim($_POST['phone'] ?? '');

if (!$order_id || !$amount || empty($phone)) {
    http_response_code(400);
    exit("❌ Missing required fields.");
}

// Normalize phone (07xxxx -> 2547xxxx)
if (preg_match('/^0\d{9}$/', $phone)) {
    $phone = '254' . substr($phone, 1);
} elseif (preg_match('/^254\d{9}$/', $phone)) {
    // OK
} else {
    exit("❌ Invalid phone format. Use 07XXXXXXXX or 2547XXXXXXXX.");
}

// Re-verify amount from DB to prevent tampering
$ord = $conn->prepare("SELECT total_amount FROM orders WHERE id = ? AND status = 'pending' LIMIT 1");
$ord->bind_param("i", $order_id);
$ord->execute();
$ord_res = $ord->get_result()->fetch_assoc();
if (!$ord_res) {
    exit("❌ Order not found or not pending.");
}
$db_amount = (float)$ord_res['total_amount'];
if (abs($db_amount - $amount) > 0.01) {
    exit("❌ Amount mismatch. Expected {$db_amount}, got {$amount}.");
}

// ---- Get access token -------------------------------------------------------
$credentials = base64_encode("$consumerKey:$consumerSecret");
$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
if ($response === false) {
    file_put_contents('mpesa_errors.log', "Token cURL error: " . curl_error($ch) . PHP_EOL, FILE_APPEND);
    exit("❌ Failed to get access token (network).");
}
curl_close($ch);

$result = json_decode($response, true);
if (empty($result['access_token'])) {
    file_put_contents('mpesa_errors.log', "Token error: " . $response . PHP_EOL, FILE_APPEND);
    exit("❌ Failed to get access token (bad response).");
}
$access_token = $result['access_token'];

// ---- STK push ---------------------------------------------------------------
$timestamp = date("YmdHis");
$password  = base64_encode($shortcode . $passkey . $timestamp);

$stk_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

$payload = [
    'BusinessShortCode' => $shortcode,
    'Password'          => $password,
    'Timestamp'         => $timestamp,
    'TransactionType'   => 'CustomerPayBillOnline',
    'Amount'            => (int)round($amount),
    'PartyA'            => $phone,
    'PartyB'            => $shortcode,
    'PhoneNumber'       => $phone,
    'CallBackURL'       => $callbackURL,
    'AccountReference'  => "GitugiOrder#$order_id",
    'TransactionDesc'   => "Payment for order #$order_id"
];

$ch = curl_init($stk_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($ch);

if ($response === false) {
    file_put_contents('mpesa_errors.log', "STK cURL error: " . curl_error($ch) . PHP_EOL, FILE_APPEND);
    exit("❌ STK Push failed (network).");
}
curl_close($ch);

$data = json_decode($response, true);
file_put_contents('mpesa_stk_responses.log', date('c') . " | ORDER#$order_id | " . $response . PHP_EOL, FILE_APPEND);

if (!isset($data['ResponseCode']) || $data['ResponseCode'] !== '0') {
    echo "❌ STK Push failed.<br><pre>" . print_r($data, true) . "</pre>";
    exit();
}

$merchantRequestID = $data['MerchantRequestID'] ?? null;
$checkoutRequestID = $data['CheckoutRequestID'] ?? null;

// Log to mpesa_payments
$stmt = $conn->prepare("
    INSERT INTO mpesa_payments
        (order_id, merchant_request_id, checkout_request_id, PhoneNumber, Amount)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("isssd", $order_id, $merchantRequestID, $checkoutRequestID, $phone, $amount);
$stmt->execute();

// Also store IDs on the order (optional but handy)
$upd = $conn->prepare("
    UPDATE orders SET mpesa_merchant_request_id = ?, mpesa_checkout_request_id = ?, phone = ?
    WHERE id = ?
");
$upd->bind_param("sssi", $merchantRequestID, $checkoutRequestID, $phone, $order_id);
$upd->execute();

// Redirect user to a status page that polls
header("Location: payment_status.php?checkout_id=" . urlencode($checkoutRequestID));
exit();
