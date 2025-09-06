<?php
include('../config/db.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in and is either an admin or seller
if (!isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// If product ID is passed
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // If the user is a seller, they can only delete their own products
    if ($_SESSION['role'] == 'seller') {
        // Ensure the product belongs to the logged-in seller
        $query = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            echo "You are not authorized to delete this product.";
            exit;
        }
    }

    // If the user is an admin, they can delete any product
    if ($_SESSION['role'] == 'admin') {
        // Admin can delete any product, no ownership check
        $query = "SELECT * FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            echo "Product not found.";
            exit;
        }
    }

    // Show confirmation prompt before deletion
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $delete_query = "DELETE FROM products WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $product_id);

        if ($delete_stmt->execute()) {
            echo "Product deleted successfully.";
            // Redirect to seller products page for sellers, admin products page for admins
            if ($_SESSION['role'] == 'seller') {
                header("Location: seller_products.php");
            } else {
                header("Location: admin_products.php");
            }
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
<body>
    <!-- Sidebar Toggle -->
    <input id="sidebar-toggle" type="checkbox">
    <label class="toggle" for="sidebar-toggle">
        <div class="bars"></div>
        <div class="bars"></div>
        <div class="bars"></div>
    </label>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Seller Menu</h3>
        <a href="seller_dashboard.php"><button>Dashboard</button></a>
        <a href="post_product.php"><button>Post New Product</button></a>
        <a href="seller_products.php"><button>My Products</button></a>
        <a href="../logout.php"><button>Logout</button></a>
    </div>

    <!-- Page Content -->
    <div class="page-content">
        <header class="header">
            <div class="logo">
                <a href="seller_dashboard.php"><h1>ThriftX Seller</h1></a>
            </div>
            <nav class="nav">
                <span class="welcome-text">Delete Product</span>
                <a href="seller_dashboard.php">Dashboard</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </header>

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
                        <h3>⚠️ Warning</h3>
                        <p>Are you sure you want to delete this product? This action cannot be undone.</p>
                    </div>
                    
                    <form method="POST" class="checkout-form">
                        <div class="form-actions">
                            <button type="submit" class="delete-confirm-btn">Yes, Delete Product</button>
                            <a href="<?= ($_SESSION['role'] == 'seller') ? 'seller_products.php' : 'admin_products.php' ?>" 
                               class="cancel-btn">Cancel</a>
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
