<?php
// mpesa_callback.php

// Set timezone
date_default_timezone_set('Africa/Nairobi');

// Read raw callback JSON from M-Pesa
$callbackJSON = file_get_contents('php://input');
$callbackData = json_decode($callbackJSON, true);

// Optional: Log the raw callback for debugging
file_put_contents('mpesa_callback.log', $callbackJSON, FILE_APPEND);

// Extract required fields safely
$resultCode = $callbackData['Body']['stkCallback']['ResultCode'] ?? null;
$resultDesc = $callbackData['Body']['stkCallback']['ResultDesc'] ?? '';
$merchantRequestId = $callbackData['Body']['stkCallback']['MerchantRequestID'] ?? '';
$checkoutRequestId = $callbackData['Body']['stkCallback']['CheckoutRequestID'] ?? '';

// Default values
$amount = null;
$receiptNumber = null;
$transactionDate = null;
$phoneNumber = null;

// If successful transaction, extract metadata
if ($resultCode == 0) {
    $items = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'];
    foreach ($items as $item) {
        switch ($item['Name']) {
            case 'Amount':
                $amount = $item['Value'];
                break;
            case 'MpesaReceiptNumber':
                $receiptNumber = $item['Value'];
                break;
            case 'TransactionDate':
                $transactionDate = DateTime::createFromFormat('YmdHis', $item['Value'])->format('Y-m-d H:i:s');
                break;
            case 'PhoneNumber':
                $phoneNumber = $item['Value'];
                break;
        }
    }
} else {
    // Optional: Handle failed transactions
    $transactionDate = date('Y-m-d H:i:s');
}

// Connect to MySQL
$host = "localhost";
$dbname = "gitugi_store";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert log into database
    $stmt = $pdo->prepare("
        INSERT INTO mpesa_transactions (
            merchant_request_id, checkout_request_id, result_code, result_desc,
            amount, mpesa_receipt_number, transaction_date, phone_number
        ) VALUES (
            :merchant_request_id, :checkout_request_id, :result_code, :result_desc,
            :amount, :mpesa_receipt_number, :transaction_date, :phone_number
        )
    ");

    $stmt->execute([
        ':merchant_request_id'   => $merchantRequestId,
        ':checkout_request_id'   => $checkoutRequestId,
        ':result_code'           => $resultCode,
        ':result_desc'           => $resultDesc,
        ':amount'                => $amount,
        ':mpesa_receipt_number'  => $receiptNumber,
        ':transaction_date'      => $transactionDate,
        ':phone_number'          => $phoneNumber
    ]);
} catch (PDOException $e) {
    file_put_contents("mpesa_errors.log", $e->getMessage() . PHP_EOL, FILE_APPEND);
}

// Send HTTP 200 response to Safaricom
header("Content-Type: application/json");
echo json_encode(["ResultCode" => 0, "ResultDesc" => "Callback received successfully"]);
?>
