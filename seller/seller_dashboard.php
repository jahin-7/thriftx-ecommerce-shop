<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    // If not logged in as a seller, redirect to login page
    header('Location: ../index.php');
    exit;
}

include('../config/db.php');  // Include database connection

// Fetch seller's products
$seller_id = $_SESSION['user_id'];
$query = "SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$products_result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - ThriftX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Facebook-style Seller Header -->
    <?php include('../includes/seller_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Seller Dashboard</h1>
                <p>Welcome back, <?= htmlspecialchars($_SESSION['first_name'] ?? 'Seller') ?>!</p>
            </div>
            <div class="page-actions">
                <a href="post_product.php" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Post New Product
                </a>
            </div>
        </div>

    <section class="products">
        <h2>Your Products</h2>
        <?php if ($products_result && $products_result->num_rows > 0): ?>
            <div class="product-list">
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <div class="product-item">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'https://via.placeholder.com/200x150?text=No+Image'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <p><?php echo htmlspecialchars($product['name']); ?></p>
                        <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : ''); ?></p>
                        <p>৳ <?php echo number_format($product['price'], 2); ?></p>
                        <div class="product-buttons">
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="view-product-btn">Edit</a>
                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="add-to-cart-btn" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <h3>No products found</h3>
                <p>You haven't posted any products yet.</p>
                <a href="post_product.php" class="view-product-btn">Post Your First Product</a>
            </div>
        <?php endif; ?>
    </section>

        <footer class="footer">
            <p>&copy; 2025 ThriftX. All rights reserved.</p>
        </footer>
    </div> <!-- End page-content -->
</body>
</html>