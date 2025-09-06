<?php
// This page is for when a product is not available.
// You may pass the product name or ID to provide more specific messages.
$product_name = isset($_GET['product']) ? $_GET['product'] : 'this product';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Unavailable</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="logo">
            <a href="../index.php">
                <h1>ThriftX</h1>
            </a>
        </div>
        <nav class="nav">
            <a href="cart.php">Cart</a>
            <a href="#">Sign In</a>
        </nav>
    </header>

    <!-- Sidebar Toggle Button -->
    <input id="checkbox" type="checkbox">
    <label class="toggle" for="checkbox">
        <div id="bar1" class="bars"></div>
        <div id="bar2" class="bars"></div>
        <div id="bar3" class="bars"></div>
    </label>

    <!-- Sidebar (Dropdown Menu) -->
    <div class="sidebar">
        <button onclick="window.location.href='electronics.php'">Electronics</button>
        <button onclick="window.location.href='clothing.php'">Clothing</button>
        <button onclick="window.location.href='furniture.php'">Furniture</button>
        <button onclick="window.location.href='services.php'">Services</button>
    </div>

    <!-- Product Unavailable Section -->
    <section class="not-available">
        <h2>Sorry, <?= $product_name; ?> is currently unavailable.</h2>
        <p>We're sorry, but the product you are looking for is no longer available or is out of stock.</p>
        <p>But don't worry! You can browse through other products on our site:</p>
        <div class="links">
            <a href="../index.php">Go to Homepage</a>
            <a href="electronics.php">Browse Electronics</a>
            <a href="clothing.php">Browse Clothing</a>
            <a href="furniture.php">Browse Furniture</a>
            <a href="services.php">Browse Services</a>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer">
        <p>&copy; 2025 ThriftX. All rights reserved.</p>
    </footer>
</body>
</html>
