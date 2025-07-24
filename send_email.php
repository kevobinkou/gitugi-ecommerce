<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/includes/PHPMailer/src/Exception.php';
require_once __DIR__ . '/includes/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/includes/PHPMailer/src/SMTP.php';

function sendOrderEmail($toEmail, $toName, $items = [], $delivery = 0, $total = 0) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yourstoreemail@gmail.com';     // ✔️ Your Gmail
        $mail->Password   = 'your-app-password';            // ✔️ App password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Email addresses
        $mail->setFrom('yourstoreemail@gmail.com', 'Gitugi Store');
        $mail->addAddress($toEmail, $toName);

        // Email body
        $mail->isHTML(true);
        $mail->Subject = '🛒 Order Confirmation - Gitugi Store';

        // Build HTML table of order items
        $itemRows = "";
        foreach ($items as $item) {
            $name = htmlspecialchars($item['name']);
            $qty = $item['quantity'];
            $price = number_format($item['price']);
            $itemRows .= "<tr><td>$name</td><td>$qty</td><td>KES $price</td></tr>";
        }

        $delivery_fmt = number_format($delivery);
        $total_fmt = number_format($total);

        $mail->Body = "
            <h3>Hello $toName,</h3>
            <p>Thank you for your order from <strong>Gitugi Store</strong>. Here are your details:</p>
            <table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>
                <thead>
                    <tr style='background:#00c853;color:white;'>
                        <th>Product</th><th>Qty</th><th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    $itemRows
                    <tr><td>Delivery</td><td>-</td><td>KES $delivery_fmt</td></tr>
                    <tr><td colspan='2'><strong>Total</strong></td><td><strong>KES $total_fmt</strong></td></tr>
                </tbody>
            </table>
            <p>We will contact you shortly to confirm delivery.</p>
            <br>
            <p>Regards,<br><strong>Gitugi Team</strong></p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        file_put_contents("mail_errors.log", $e->getMessage() . PHP_EOL, FILE_APPEND);
        return false;
    }
}
