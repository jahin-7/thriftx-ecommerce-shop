<?php
require_once('../includes/auth.php');
requireLogin();

include('../config/db.php');

// Get current user info
$user = getCurrentUser();

// Fetch featured products for display
$query = "SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 8";
$products_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ThriftX - Your Thrift Store</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="customer-layout <?= (($_SESSION['theme'] ?? 'dark') === 'light') ? 'light-theme' : '' ?>">
    <?php include('../includes/customer_sidebar.php'); ?>

    <!-- Page Content -->
    <div class="page-content customer-page-content">

        <!-- Header -->
        <?php include('../includes/customer_header.php'); ?>
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1>Welcome to ThriftX</h1>
                <p>Hello, <?= htmlspecialchars($user['first_name']) ?>! Discover amazing deals today</p>
            </div>
        </div>

        <section class="search">
            <div class="search-container">
                <form action="search_results.php" method="GET">
                    <input type="text" class="search-bar" name="query" placeholder="Search for anything..." required>
                    <button type="submit" class="search-button">Search</button>
                </form>
            </div>
        </section>

    <section class="products">
        <h2>Featured Products</h2>
        <div class="product-list">
            <?php if ($products_result && $products_result->num_rows > 0): ?>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <div class="product-item">
                        <img src="<?php echo !empty($product['image_url']) ? htmlspecialchars('../seller/' . $product['image_url']) : 'https://via.placeholder.com/200x150?text=No+Image'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <p><?php echo htmlspecialchars($product['name']); ?></p>
                        <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : ''); ?></p>
                        <p>৳ <?php echo number_format($product['price'], 2); ?></p>
                        <div class="product-buttons">
                            <a href="product_page.php?id=<?php echo $product['id']; ?>" class="view-product-btn">View Details</a>
                            <form action="cart.php" method="POST" style="display: contents;">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                                <input type="hidden" name="name" value="<?= htmlspecialchars($product['name']); ?>">
                                <input type="hidden" name="price" value="<?= $product['price']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-products">
                    <h3>No products available at the moment</h3>
                    <p>Check back later for new items!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

        <footer class="footer">
            <p>&copy; 2025 ThriftX. All rights reserved.</p>
        </footer>
    </div> <!-- End page-content -->
</body>
</html>
