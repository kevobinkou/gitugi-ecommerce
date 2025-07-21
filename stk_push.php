<?php
date_default_timezone_set('Africa/Nairobi');

// M-Pesa Credentials
$consumerKey = "QEf8LGMFgtqbhZOVU0p4FOrg1et60i2qhPWY8qYvY8rv8vEP";
$consumerSecret = "bhU2jT2bu7kAn1E8cd8p8g6ZCGCkjcqXoz6WexLggn0zWJGBHhHWiDCDI5KilZ8J";

// Get phone and amount
$phone = $_POST['phone'];
$amount = $_POST['amount'];

// Format phone
if (substr($phone, 0, 1) === '0') {
    $phone = '254' . substr($phone, 1);
}

// Get access token
$credentials = base64_encode("$consumerKey:$consumerSecret");
$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic '.$credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response);
if (!isset($result->access_token)) {
    die("❌ Failed to get access token. Please check your credentials.");
}
$access_token = $result->access_token;

// STK push request
$shortcode = "174379";
$passkey = "bfb279f9aa9bdbcf15..."; // Use full passkey
$timestamp = date("YmdHis");
$password = base64_encode($shortcode.$passkey.$timestamp);

$stk_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

$callbackURL = "https://yourdomain.com/callback_url.php"; // Replace with your real URL

$curl_post_data = [
    'BusinessShortCode' => $shortcode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => $shortcode,
    'PhoneNumber' => $phone,
    'CallBackURL' => $callbackURL,
    'AccountReference' => 'GitugiCheckout',
    'TransactionDesc' => 'Payment for foodstuffs'
];

$ch = curl_init($stk_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['ResponseCode']) && $data['ResponseCode'] == '0') {
    // Redirect to receipt page with checkout request ID
    $checkoutRequestID = $data['CheckoutRequestID'];
    header("Location: receipt.php?checkout_id=" . urlencode($checkoutRequestID));
    exit();
} else {
    echo "❌ STK Push failed. Try again later.";
}
?>
