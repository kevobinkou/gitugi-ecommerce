<?php
session_start();
include("includes/db.php");
require_once 'includes/mailer.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['cart'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $total = 0;
    $orderDetailsHTML = '';

    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $result = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $subtotal = $row['price'] * $quantity;
            $total += $subtotal;
            $name_html = htmlspecialchars($row['name']);
            $orderDetailsHTML .= "<li>$name_html x $quantity (Ksh " . number_format($subtotal, 2) . ")</li>";
        }
    }

    $query = "INSERT INTO orders (customer_name, customer_phone, customer_location, total_amount, status)
              VALUES ('$name', '$phone', '$location', '$total', 'Pending')";

    if (mysqli_query($conn, $query)) {
        $order_id = mysqli_insert_id($conn);

        // Insert order items
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity)
                                 VALUES ($order_id, $product_id, $quantity)");
        }

        // Send email to admin
        $emailSent = sendOrderEmail($name, $phone, $location, $orderDetailsHTML, $total, 'youradmin@example.com');

        // Clear cart
        unset($_SESSION['cart']);

        echo "<script>alert('Order placed successfully" . ($emailSent ? " and email sent" : "") . "!');</script>";
        echo "<script>window.location.href = 'thank_you.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error placing order.');</script>";
        echo "<script>window.history.back();</script>";
    }
} else {
    echo "<script>alert('No items in cart or invalid request.');</script>";
    echo "<script>window.location.href = 'index.php';</script>";
}
?>
