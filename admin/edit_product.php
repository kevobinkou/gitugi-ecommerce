<?php
session_start();
include("includes/db.php");

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Ensure product ID is provided
if (!isset($_GET['id'])) {
    die("❌ Product ID not specified.");
}

$product_id = intval($_GET['id']);

// Fetch existing product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("❌ Product not found.");
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category = $_POST['category'];

    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, stock = ?, category = ? WHERE id = ?");
    $stmt->bind_param("sdiss", $name, $price, $stock, $category, $product_id);

    if ($stmt->execute()) {
        header("Location: manage_products.php?success=1");
        exit();
    } else {
        $error = "❌ Failed to update product.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #121212;
            color: #f1f1f1;
            padding: 40px;
        }
        .form-box {
            background: #1e1e1e;
            padding: 25px;
            border-radius: 12px;
            max-width: 500px;
            margin: auto;
        }
        input, select, button {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 12px;
            border-radius: 6px;
            border: 1px solid #444;
            background: #2c2c2c;
            color: white;
        }
        button {
            background: #00e676;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background: #00c853;
        }
        h2 {
            text-align: center;
            color: #00e676;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Edit Product</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label>Product Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

        <label>Price (KES)</label>
        <input type="number" name="price" value="<?= $product['price'] ?>" step="0.01" required>

        <label>Stock</label>
        <input type="number" name="stock" value="<?= $product['stock'] ?>" required>

        <label>Category</label>
        <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>

        <button type="submit">Update Product</button>
    </form>
</div>

</body>
</html>
