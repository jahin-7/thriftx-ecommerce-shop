<?php
require_once('../includes/auth.php');
require_once('../includes/cart_functions.php');
requireLogin();

// Get current user info
$user = getCurrentUser();

// Handle adding items to the cart
if (isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    if ($cartManager->addToCart($user['id'], $product_id, $quantity)) {
        $_SESSION['success_message'] = 'Item added to cart successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to add item to cart.';
    }
    
    header('Location: cart.php');
    exit;
}

// Handle updating quantities
if (isset($_POST['action']) && $_POST['action'] == 'update_quantity') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    if ($cartManager->updateQuantity($user['id'], $product_id, $quantity)) {
        $_SESSION['success_message'] = 'Quantity updated successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to update quantity.';
    }
    
    header('Location: cart.php');
    exit;
}

// Handle removing items from the cart
if (isset($_POST['action']) && $_POST['action'] == 'remove_from_cart') {
    $product_id = $_POST['product_id'];
    
    if ($cartManager->removeFromCart($user['id'], $product_id)) {
        $_SESSION['success_message'] = 'Item removed from cart successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to remove item from cart.';
    }
    
    header('Location: cart.php');
    exit;
}

// Get cart items from database
$cart_items = $cartManager->getCartItems($user['id']);
$subtotal = $cartManager->getCartTotal($user['id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - ThriftX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Page Content -->
    <div class="page-content customer-page-content">
        <?php include('../includes/customer_header.php'); ?>
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1>Shopping Cart</h1>
                <p>Review your items before checkout</p>
            </div>
            <div class="page-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    </svg>
                    Continue Shopping
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

        <!-- Cart Section -->
        <div class="cart-container">
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                    </div>
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="dashboard.php" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        </svg>
                        Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="cart-grid">
                    <div class="cart-items">
                        <h3>Cart Items (<?= count($cart_items); ?>)</h3>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-product-id="<?= $item['product_id']; ?>">
                                <div class="cart-item-image">
                                    <img src="<?= !empty($item['image_url']) ? '../seller/uploads/' . $item['image_url'] : 'https://via.placeholder.com/120x120?text=Product'; ?>" 
                                         alt="<?= htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="cart-item-details">
                                    <h4 class="cart-item-title"><?= htmlspecialchars($item['name']); ?></h4>
                                    <p class="cart-item-price">৳<?= number_format($item['price'], 2); ?></p>
                                </div>
                                <div class="quantity-controls">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                        <input type="hidden" name="quantity" value="<?= max(1, $item['quantity'] - 1); ?>">
                                        <button type="submit" class="quantity-btn">-</button>
                                    </form>
                                    <span class="quantity-display"><?= $item['quantity']; ?></span>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                        <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1; ?>">
                                        <button type="submit" class="quantity-btn">+</button>
                                    </form>
                                </div>
                                <div class="item-total" data-product-id="<?= $item['product_id']; ?>">
                                    ৳<?= number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="remove_from_cart">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                    <button type="submit" class="remove-btn" onclick="return confirm('Are you sure you want to remove this item?')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3,6 5,6 21,6"></polyline>
                                            <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-line">
                            <span>Subtotal:</span>
                            <span id="total-amount">৳<?= number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="summary-line">
                            <span>Shipping:</span>
                            <span>৳5.00</span>
                        </div>
                        <div class="summary-line total">
                            <span>Total:</span>
                            <span id="final-total">৳<?= number_format($subtotal + 5, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-primary checkout-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 12l2 2 4-4"></path>
                                <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"></path>
                                <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"></path>
                            </svg>
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<!-- No JavaScript - Pure HTML/CSS/PHP -->
</body>
</html>
