<?php
include("includes/db.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Orders - Gitugi E-commerce</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 2em;
        }
        .order {
            border: 1px solid #ddd;
            padding: 1em;
            margin-bottom: 1em;
        }
        .items {
            margin-left: 1em;
            background: #f9f9f9;
            padding: 1em;
        }
    </style>
</head>
<body>

<h2>All Orders</h2>

<?php
$order_query = "SELECT * FROM orders ORDER BY order_time DESC";
$order_result = mysqli_query($conn, $order_query);

while ($order = mysqli_fetch_assoc($order_result)) {
    echo "<div class='order'>";
    echo "<strong>Order #{$order['id']}</strong><br>";
    echo "Customer: {$order['customer_name']}<br>";
    echo "Phone: {$order['phone']}<br>";
    echo "Location: {$order['location']}<br>";
    echo "Total: Ksh {$order['total_amount']}<br>";
    echo "Date: {$order['order_time']}<br><br>";

    // Get order items
    $order_id = $order['id'];
    $items_query = "
        SELECT p.name, oi.quantity, oi.price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = $order_id
    ";
    $items_result = mysqli_query($conn, $items_query);

    echo "<div class='items'><strong>Items:</strong><ul>";
    while ($item = mysqli_fetch_assoc($items_result)) {
        $name = $item['name'];
        $qty = $item['quantity'];
        $price = $item['price'];
        echo "<li>$name - Ksh $price x $qty = Ksh " . ($price * $qty) . "</li>";
    }
    echo "</ul></div>";
    echo "</div>";
}
?>

</body>
</html>
