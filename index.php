<?php
include("includes/db.php");
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Gitugi E-commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #121212;
            color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #1f1f1f;
            padding: 20px;
            text-align: center;
        }

        header h1 {
            margin: 0;
            font-size: 2rem;
            color: #ffffff;
        }

        .cart-link {
            display: inline-block;
            margin-top: 10px;
            background-color: #00c853;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .cart-link:hover {
            background-color: #00e676;
        }

        .products {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }

        .product {
            background-color: #1e1e1e;
            border-radius: 12px;
            margin: 10px;
            width: 220px;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.4);
            text-align: center;
        }

        .product img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product h4 {
            margin: 10px 0 5px;
            font-size: 1.1rem;
            color: #00e676;
        }

        .product p {
            margin: 5px 0;
        }

        .product form button {
            background-color: #00c853;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .product form button:hover {
            background-color: #00e676;
        }

        @media (max-width: 600px) {
            .product {
                width: 90%;
            }

            header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Welcome to Gitugi E-commerce</h1>
    <a href="cart.php" class="cart-link">🛒 View Cart</a>
</header>

<main class="products">
    <?php
    $query = "SELECT * FROM products";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
    ?>
        <div class="product">
            <img src="assets/images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
            <h4><?php echo $row['name']; ?></h4>
            <p>Ksh <?php echo $row['price']; ?></p>
            <p><small><?php echo $row['category']; ?></small></p>
            <form method="POST" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                <button type="submit">Add to Cart</button>
            </form>
        </div>
    <?php } ?>
</main>

</body>
</html>
