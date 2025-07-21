<?php
// callback_url.php — receives M-Pesa STK Push response from Safaricom

// Read the raw JSON payload from Safaricom
$callbackJSON = file_get_contents('php://input');

// Decode the JSON
$callbackData = json_decode($callbackJSON, true);

// Log raw payload (for debugging)
file_put_contents("stk_callback_log.txt", $callbackJSON . PHP_EOL, FILE_APPEND);

// Check if the body and stkCallback exist
if (isset($callbackData['Body']['stkCallback'])) {
    $stkCallback = $callbackData['Body']['stkCallback'];
    $merchantRequestID = $stkCallback['MerchantRequestID'] ?? '';
    $checkoutRequestID = $stkCallback['CheckoutRequestID'] ?? '';
    $resultCode = $stkCallback['ResultCode'];
    $resultDesc = $stkCallback['ResultDesc'];

    // Connect to your database
    $conn = new mysqli("localhost", "root", "", "gitugi_db");
    if ($conn->connect_error) {
        file_put_contents("stk_callback_log.txt", "DB Connection failed: " . $conn->connect_error . PHP_EOL, FILE_APPEND);
        exit;
    }

    // If transaction is successful, update status and log
    if ($resultCode == 0 && isset($stkCallback['CallbackMetadata'])) {
        $metadata = $stkCallback['CallbackMetadata']['Item'];
        $amount = $phone = $mpesaReceiptNumber = $transactionDate = '';

        foreach ($metadata as $item) {
            switch ($item['Name']) {
                case 'Amount':
                    $amount = $item['Value'];
                    break;
                case 'MpesaReceiptNumber':
                    $mpesaReceiptNumber = $item['Value'];
                    break;
                case 'TransactionDate':
                    $transactionDate = $item['Value'];
                    break;
                case 'PhoneNumber':
                    $phone = $item['Value'];
                    break;
            }
        }

        // Insert into mpesa_payments table
        $stmt = $conn->prepare("INSERT INTO mpesa_payments (merchant_request_id, checkout_request_id, result_code, result_desc, amount, mpesa_receipt_number, transaction_date, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisdsss", $merchantRequestID, $checkoutRequestID, $resultCode, $resultDesc, $amount, $mpesaReceiptNumber, $transactionDate, $phone);
        $stmt->execute();
        $stmt->close();

    } else {
        // Log failed transaction
        $stmt = $conn->prepare("INSERT INTO mpesa_payments (merchant_request_id, checkout_request_id, result_code, result_desc) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $merchantRequestID, $checkoutRequestID, $resultCode, $resultDesc);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
} else {
    file_put_contents("stk_callback_log.txt", "Invalid JSON structure: " . $callbackJSON . PHP_EOL, FILE_APPEND);
}
?>
