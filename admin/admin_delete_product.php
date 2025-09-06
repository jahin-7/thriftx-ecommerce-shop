<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = '';
$error_message = '';

// Get product details
$product_query = "SELECT p.*, u.first_name, u.last_name 
                  FROM products p 
                  LEFT JOIN users u ON p.seller_id = u.id 
                  WHERE p.id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: admin_products.php');
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    // Delete the product
    $delete_query = "DELETE FROM products WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $product_id);
    
    if ($delete_stmt->execute()) {
        // Delete the image file if it exists
        if ($product['image_url'] && file_exists($product['image_url'])) {
            unlink($product['image_url']);
        }
        
        $_SESSION['success'] = "Product deleted successfully!";
        header('Location: admin_products.php');
        exit;
    } else {
        $error_message = "Error deleting product: " . $delete_stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product - ThriftX Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="checkout-section">
            <div class="admin-page-header">
                <h2>Delete Product</h2>
                <a href="admin_products.php" class="back-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Products
                </a>
            </div>

            <!-- Display error message -->
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <!-- Product Preview -->
            <div class="product-preview">
                <div class="preview-header">
                    <h3>Product to Delete</h3>
                    <p>Review the product details before confirming deletion</p>
                </div>
                
                <div class="preview-content">
                    <div class="product-image">
                        <?php if ($product['image_url'] && file_exists($product['image_url'])): ?>
                            <img src="../<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <div class="no-image">
                                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21,15 16,10 5,21"></polyline>
                                </svg>
                                <span>No Image</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-details">
                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                        <p class="product-category"><?= ucfirst($product['category']) ?></p>
                        <p class="product-price">৳ <?= number_format($product['price'], 2) ?></p>
                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                        
                        <?php if (!empty($product['specifications'])): ?>
                            <div class="product-specs">
                                <h5>Specifications:</h5>
                                <p><?= htmlspecialchars($product['specifications']) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-meta">
                            <p><strong>Seller:</strong> <?= htmlspecialchars($product['first_name'] . ' ' . $product['last_name']) ?></p>
                            <p><strong>Created:</strong> <?= date('M d, Y', strtotime($product['created_at'])) ?></p>
                            <p><strong>Status:</strong> <span class="status-<?= $product['status'] ?>"><?= ucfirst($product['status']) ?></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Warning -->
            <div class="delete-warning">
                <div class="warning-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div class="warning-content">
                    <h3>Warning: This action cannot be undone!</h3>
                    <p>You are about to permanently delete this product. This will:</p>
                    <ul>
                        <li>Remove the product from the database</li>
                        <li>Delete the product image file</li>
                        <li>Remove any associated order items</li>
                        <li>This action cannot be reversed</li>
                    </ul>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="admin_products.php" class="cancel-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Cancel
                </a>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="confirm_delete" value="1">
                    <button type="submit" class="delete-confirm-btn" onclick="return confirm('Are you absolutely sure you want to delete this product? This action cannot be undone!')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3,6 5,6 21,6"></polyline>
                            <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                        </svg>
                        Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
