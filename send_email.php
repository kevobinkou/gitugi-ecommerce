<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

function sendOrderEmail($toEmail, $toName, $product, $qty, $total) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Or your mail server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yourstoreemail@gmail.com'; // Use your email
        $mail->Password   = 'your-app-password';        // Use app password from Gmail settings
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender and recipient
        $mail->setFrom('yourstoreemail@gmail.com', 'Gitugi Store');
        $mail->addAddress($toEmail, $toName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = '🛒 Order Confirmation - Gitugi Store';
        $mail->Body    = "
            <h3>Hello $toName,</h3>
            <p>Thank you for your order from <strong>Gitugi Store</strong>.</p>
            <p><strong>Product:</strong> $product<br>
            <strong>Quantity:</strong> $qty<br>
            <strong>Total:</strong> KES $total</p>
            <p>We will contact you shortly to confirm delivery.</p>
            <br>
            <p>Regards,<br>Gitugi Team</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
