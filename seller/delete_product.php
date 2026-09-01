<?php
include('../config/db.php');
require_once('../includes/activity_log.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sellers manage their own products through this page (admin uses admin/admin_delete_product.php)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../index.php');
    exit;
}

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    $query = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        echo "You are not authorized to delete this product.";
        exit;
    }

    // Show confirmation prompt before deletion
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $delete_query = "DELETE FROM products WHERE id = ? AND seller_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("ii", $product_id, $_SESSION['user_id']);

        if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {
            if (!empty($product['image_url'])) {
                $image_path = '../seller/' . $product['image_url'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            logActivity($conn, $_SESSION['user_id'], 'product_deleted', 'product', $product_id, $product['name']);
            $_SESSION['success'] = "Product deleted successfully.";
            header("Location: seller_products.php");
            exit;
        } else {
            echo "Error deleting product.";
        }
    }
} else {
    echo "No product ID provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product - ThriftX Seller</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="<?= (($_SESSION['theme'] ?? 'dark') === 'light') ? 'light-theme' : '' ?>">
    <?php include('../includes/seller_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <!-- Delete Confirmation Section -->
        <section class="delete-product">
            <div class="checkout-section">
                <h2>Delete Product</h2>

                <?php if (isset($product)): ?>
                    <div class="product-preview">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($product['image_url']); ?>"
                                 alt="<?= htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                        </div>
                        <div class="product-details">
                            <h3><?= htmlspecialchars($product['name']); ?></h3>
                            <p class="product-price">৳ <?= number_format($product['price'], 2); ?></p>
                            <p class="product-category"><?= ucfirst($product['category']); ?></p>
                            <p class="product-description"><?= htmlspecialchars($product['description']); ?></p>
                        </div>
                    </div>

                    <div class="delete-warning">
                        <h3>Warning</h3>
                        <p>Are you sure you want to delete this product? This action cannot be undone.</p>
                    </div>

                    <form method="POST" class="checkout-form">
                        <div class="form-actions">
                            <button type="submit" class="delete-confirm-btn">Yes, Delete Product</button>
                            <a href="seller_products.php" class="cancel-btn">Cancel</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="error-message">
                        <h3>Product Not Found</h3>
                        <p>The product you're trying to delete doesn't exist or you don't have permission to delete it.</p>
                        <a href="seller_products.php" class="cart-checkout-btn">Back to My Products</a>
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
