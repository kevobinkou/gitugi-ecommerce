<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

function sendOrderEmail($customerName, $customerPhone, $customerLocation, $orderDetails, $totalAmount, $recipientEmail) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kelmaina4837@gmail.com';         
        $mail->Password   = 'qtqb ywws wjto mipy';          
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('kelmaina4837@gmail.com', 'Gitugi E-commerce'); 
        $mail->addAddress($recipientEmail);                         // Admin or customer email

        // Content
        $mail->isHTML(true);
        $mail->Subject = '🛒 New Order Received';

        $mail->Body = "
            <h2>New Order from $customerName</h2>
            <p><strong>Phone:</strong> $customerPhone</p>
            <p><strong>Location:</strong> $customerLocation</p>
            <h4>Order Details:</h4>
            <ul>$orderDetails</ul>
            <p><strong>Total Amount:</strong> Ksh " . number_format($totalAmount, 2) . "</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Email error: {$mail->ErrorInfo}");
        return false;
    }
}
