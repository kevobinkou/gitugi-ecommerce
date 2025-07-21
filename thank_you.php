<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Successful - Gitugi E-commerce</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --primary: #38b000;     /* calm green */
      --bg: #f0fdf4;           /* soft mint background */
      --text: #333;
      --accent: #70e000;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: var(--bg);
      margin: 0;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .thank-you-box {
      background: #fff;
      padding: 40px 30px;
      border-radius: 16px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
      text-align: center;
      max-width: 420px;
      width: 90%;
    }

    .thank-you-box h1 {
      color: var(--primary);
      font-size: 26px;
      margin-bottom: 10px;
    }

    .thank-you-box p {
      color: var(--text);
      font-size: 16px;
    }

    .thank-you-box a {
      margin-top: 25px;
      display: inline-block;
      background-color: var(--primary);
      color: #fff;
      text-decoration: none;
      padding: 12px 20px;
      font-size: 15px;
      border-radius: 8px;
      transition: background-color 0.3s ease;
    }

    .thank-you-box a:hover {
      background-color: var(--accent);
      color: #000;
    }

    @media (max-width: 600px) {
      .thank-you-box {
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body>

  <div class="thank-you-box">
    <h1>✅ Thank You for Your Order!</h1>
    <p>Your order has been placed successfully.</p>
    <a href="index.php">Continue Shopping</a>
  </div>

</body>
</html>
