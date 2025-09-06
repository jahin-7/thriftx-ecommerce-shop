<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');  // Include database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Fetch products posted by the logged-in seller
$query = "SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - ThriftX Seller</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Facebook-style Seller Header -->
    <?php include('../includes/seller_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="page-header">
            <div class="page-title">
                <h1>My Products</h1>
                <p>Manage your product listings and inventory</p>
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

        <!-- Product Management Section -->
        <section class="seller-products">
            <div class="checkout-section">
                <h2>Manage Your Products</h2>
                
                <?php if ($result->num_rows > 0): ?>
                    <div class="products-grid">
                        <?php while ($product = $result->fetch_assoc()): ?>
                            <div class="product-item">
                                <div class="product-image">
                                    <img src="<?= htmlspecialchars($product['image_url']); ?>" 
                                         alt="<?= htmlspecialchars($product['name']); ?>"
                                         onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                                </div>
                                <div class="product-info">
                                    <h3><?= htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-price">৳ <?= number_format($product['price'], 2); ?></p>
                                    <p class="product-category"><?= ucfirst($product['category']); ?></p>
                                    <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                                    <div class="product-actions">
                                        <a href="edit_product.php?id=<?= $product['id']; ?>" class="edit-btn">Edit</a>
                                        <a href="delete_product.php?id=<?= $product['id']; ?>" class="delete-btn" 
                                           onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No Products Yet</h3>
                        <p>You haven't posted any products yet. Start by adding your first product!</p>
                        <a href="post_product.php" class="cart-checkout-btn">Post Your First Product</a>
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
