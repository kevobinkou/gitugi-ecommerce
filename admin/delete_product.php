<?php
session_start();
include("includes/db.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Check if product_id is provided
if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    // Delete product from database
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete product.";
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid product ID.";
}

$conn->close();

// Redirect back to product listing
header("Location: admin_dashboard.php");
exit();
?>
