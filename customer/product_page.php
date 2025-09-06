<?php
require_once('../includes/auth.php');
require_once('../includes/cart_functions.php');
requireLogin();

// Get current user info
$user = getCurrentUser();

// Get the product ID from the URL
$product_id = $_GET['id'];

// Fetch the product details based on the product ID
$query = "SELECT * FROM products WHERE id = ? AND status = 'active'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

// Check if the product exists
if (!$product) {
    echo "Product not found.";
    exit;
}

// Handle adding to cart
if (isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
    $quantity = $_POST['quantity'];
    
    if ($cartManager->addToCart($user['id'], $product_id, $quantity)) {
        $_SESSION['success_message'] = 'Item added to cart successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to add item to cart.';
    }
    
    header('Location: product_page.php?id=' . $product_id);
    exit;
}

$image_query = "SELECT * FROM product_images WHERE product_id = ?";
$image_stmt = $conn->prepare($image_query);
$image_stmt->bind_param("i", $product_id);
$image_stmt->execute();
$images_result = $image_stmt->get_result();

$review_query = "SELECT * FROM product_reviews WHERE product_id = ?";
$review_stmt = $conn->prepare($review_query);
$review_stmt->bind_param("i", $product_id);
$review_stmt->execute();
$reviews_result = $review_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product['name']; ?> - ThriftX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Page Content -->
    <div class="page-content customer-page-content">
        <?php include('../includes/customer_header.php'); ?>
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1><?= htmlspecialchars($product['name']); ?></h1>
                <p>Product Details</p>
            </div>
            <div class="page-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error_message']; ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Product Details Section -->
        <div class="product-details-container">
            <div class="product-image-section">
                <?php if ($images_result->num_rows > 0): ?>
                    <?php while ($image = $images_result->fetch_assoc()): ?>
                        <img src="<?= $image['image_url']; ?>" alt="<?= $product['name']; ?>" class="product-main-image">
                    <?php endwhile; ?>
                <?php else: ?>
                    <img src="<?= !empty($product['image']) ? '../seller/uploads/' . $product['image'] : 'https://via.placeholder.com/400x400?text=No+Image'; ?>" 
                         alt="<?= $product['name']; ?>" class="product-main-image">
                <?php endif; ?>
            </div>

            <div class="product-info-section">
                <h2 class="product-title"><?= htmlspecialchars($product['name']); ?></h2>
                <p class="product-price">৳<?= number_format($product['price'], 2); ?></p>
                <p class="product-description"><?= htmlspecialchars($product['description']); ?></p>
                
                <?php if (!empty($product['specifications'])): ?>
                    <div class="product-specifications">
                        <h3>Specifications</h3>
                        <p><?= htmlspecialchars($product['specifications']); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Add to Cart Form -->
                <form action="product_page.php?id=<?= $product['id']; ?>" method="POST" class="add-to-cart-form">
                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="99" class="quantity-input">
                    </div>

                    <button type="submit" name="action" value="add_to_cart" class="btn btn-primary add-to-cart-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Add to Cart
                    </button>
                </form>
            </div>
        </div>


        <!-- Product Reviews Section -->
        <div class="product-reviews-section">
            <h3>Customer Reviews</h3>
            <?php if ($reviews_result->num_rows > 0): ?>
                <div class="reviews-list">
                    <?php while ($review = $reviews_result->fetch_assoc()): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <h4><?= htmlspecialchars($review['customer_name']); ?></h4>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= $i <= ($review['rating'] ?? 5) ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="review-text"><?= htmlspecialchars($review['review']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-reviews">
                    <p>No reviews yet. Be the first to review this product!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<!-- No JavaScript - Pure HTML/CSS/PHP -->
</body>
</html>
