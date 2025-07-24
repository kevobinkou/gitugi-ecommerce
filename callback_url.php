<?php
// callback_url.php — receives M-Pesa STK Push response from Safaricom

date_default_timezone_set('Africa/Nairobi');

// Read the raw JSON payload from Safaricom
$callbackJSON = file_get_contents('php://input');

// Decode JSON
$callbackData = json_decode($callbackJSON, true);

// Log raw payload
file_put_contents("stk_callback_log.txt", $callbackJSON . PHP_EOL, FILE_APPEND);

// Check structure
if (!isset($callbackData['Body']['stkCallback'])) {
    file_put_contents("stk_callback_log.txt", "❌ Invalid JSON structure" . PHP_EOL, FILE_APPEND);
    exit;
}

$stkCallback = $callbackData['Body']['stkCallback'];
$merchantRequestID  = $stkCallback['MerchantRequestID'] ?? '';
$checkoutRequestID  = $stkCallback['CheckoutRequestID'] ?? '';
$resultCode         = $stkCallback['ResultCode'] ?? -1;
$resultDesc         = $stkCallback['ResultDesc'] ?? '';

// Include DB connection
$conn = new mysqli("localhost", "root", "", "gitugi_db");
// $conn = new mysqli("b4j0t8ksvqfb4e6hawxj-mysql.services.clever-cloud.com", "unmichyijl7pyhqo", "yourpass", "bgibcg49xl3lpmcwcdz7");

if ($conn->connect_error) {
    file_put_contents("stk_callback_log.txt", "❌ DB Connect Error: " . $conn->connect_error . PHP_EOL, FILE_APPEND);
    exit;
}

// Successful transaction
if ($resultCode == 0 && isset($stkCallback['CallbackMetadata'])) {
    $metadata = $stkCallback['CallbackMetadata']['Item'];

    $amount = $phone = $mpesaReceiptNumber = '';
    $transactionDate = date('Y-m-d H:i:s'); // default fallback

    foreach ($metadata as $item) {
        switch ($item['Name']) {
            case 'Amount':
                $amount = $item['Value'];
                break;
            case 'MpesaReceiptNumber':
                $mpesaReceiptNumber = $item['Value'];
                break;
            case 'TransactionDate':
                $rawDate = $item['Value'];
                $dt = DateTime::createFromFormat('YmdHis', $rawDate);
                $transactionDate = $dt ? $dt->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
                break;
            case 'PhoneNumber':
                $phone = $item['Value'];
                break;
        }
    }

    // Insert full record
    $stmt = $conn->prepare("INSERT INTO mpesa_payments (
        merchant_request_id, checkout_request_id, result_code, result_desc,
        amount, mpesa_receipt_number, transaction_date, phone
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssisdsss", $merchantRequestID, $checkoutRequestID, $resultCode, $resultDesc,
        $amount, $mpesaReceiptNumber, $transactionDate, $phone);

    $stmt->execute();
    $stmt->close();

} else {
    // Failed transaction, insert minimal log
    $stmt = $conn->prepare("INSERT INTO mpesa_payments (
        merchant_request_id, checkout_request_id, result_code, result_desc
    ) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $merchantRequestID, $checkoutRequestID, $resultCode, $resultDesc);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// Send Safaricom success response
header("Content-Type: application/json");
echo json_encode([
    "ResultCode" => 0,
    "ResultDesc" => "Callback received successfully"
]);
?>
