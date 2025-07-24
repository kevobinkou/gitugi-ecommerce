<?php
// Set timezone
date_default_timezone_set('Africa/Nairobi');

// Read raw callback JSON
$callbackJSON = file_get_contents('php://input');
$callbackData = json_decode($callbackJSON, true);

// Optional: log the raw callback
file_put_contents('mpesa_callback.log', $callbackJSON . PHP_EOL, FILE_APPEND);

// Extract details
$resultCode = $callbackData['Body']['stkCallback']['ResultCode'] ?? null;
$resultDesc = $callbackData['Body']['stkCallback']['ResultDesc'] ?? '';
$checkoutRequestId = $callbackData['Body']['stkCallback']['CheckoutRequestID'] ?? '';

$amount = null;
$receiptNumber = null;
$transactionDate = null;
$phoneNumber = null;

if ($resultCode == 0 && isset($callbackData['Body']['stkCallback']['CallbackMetadata'])) {
    foreach ($callbackData['Body']['stkCallback']['CallbackMetadata']['Item'] as $item) {
        switch ($item['Name']) {
            case 'Amount': $amount = $item['Value']; break;
            case 'MpesaReceiptNumber': $receiptNumber = $item['Value']; break;
            case 'TransactionDate': $transactionDate = DateTime::createFromFormat('YmdHis', $item['Value'])->format('Y-m-d H:i:s'); break;
            case 'PhoneNumber': $phoneNumber = $item['Value']; break;
        }
    }
} else {
    $transactionDate = date('Y-m-d H:i:s');
}

// Connect to Clever Cloud DB
$host = "b4j0t8ksvqfb4e6hawxj-mysql.services.clever-cloud.com";
$dbname = "bgibcg49xl3lpmcwcdz7";
$user = "unmichyijl7pyhqo";
$pass = "your_database_password"; // 🔐 Replace with your Clever Cloud DB password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Update mpesa_payments table
    $stmt = $pdo->prepare("
        UPDATE mpesa_payments
        SET ResultCode = :result_code,
            ResultDesc = :result_desc,
            MpesaReceiptNumber = :receipt,
            TransactionDate = :txdate,
            PhoneNumber = :phone
        WHERE CheckoutRequestID = :checkout_id
    ");
    $stmt->execute([
        ':result_code' => $resultCode,
        ':result_desc' => $resultDesc,
        ':receipt'     => $receiptNumber,
        ':txdate'      => $transactionDate,
        ':phone'       => $phoneNumber,
        ':checkout_id' => $checkoutRequestId
    ]);

    // Get order_id from payment record
    $orderQuery = $pdo->prepare("SELECT order_id FROM mpesa_payments WHERE CheckoutRequestID = ?");
    $orderQuery->execute([$checkoutRequestId]);
    $row = $orderQuery->fetch(PDO::FETCH_ASSOC);

    if ($row && $resultCode == 0) {
        $order_id = $row['order_id'];

        // Mark order as paid
        $updateOrder = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
        $updateOrder->execute([$order_id]);
    }

} catch (PDOException $e) {
    file_put_contents("mpesa_errors.log", $e->getMessage() . PHP_EOL, FILE_APPEND);
}

// Reply to Safaricom
header("Content-Type: application/json");
echo json_encode(["ResultCode" => 0, "ResultDesc" => "Callback received successfully"]);
?>
