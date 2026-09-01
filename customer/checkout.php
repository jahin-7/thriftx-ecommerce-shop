<?php
require_once('../includes/auth.php');
require_once('../includes/cart_functions.php');
requireLogin();

$user = getCurrentUser();

// Pull the real cart from the database, only items still actually purchasable
$all_cart_items = $cartManager->getCartItems($user['id']);
$cart_items = array_values(array_filter($all_cart_items, function ($item) {
    return $item['status'] === 'active';
}));

// If there's nothing purchasable, send them back to the cart
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// Handle the checkout form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate user inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $payment_method = trim($_POST['payment_method'] ?? 'cash_on_delivery');

    if (empty($name) || empty($email) || empty($address) || empty($phone)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Invalid phone number. It should be 10 digits.";
    }

    // If there's an error, return to the checkout page with an error message
    if (isset($error)) {
        $_SESSION['error'] = $error;
        header('Location: checkout.php');
        exit;
    }

    // Re-fetch the cart fresh at submit time, in case something sold out
    // between page load and form submission
    $submit_cart_items = $cartManager->getCartItems($user['id']);
    $unavailable_at_submit = array_filter($submit_cart_items, function ($item) {
        return $item['status'] !== 'active';
    });

    if (!empty($unavailable_at_submit)) {
        $_SESSION['error'] = 'One or more items in your cart sold out while you were checking out. Please review your cart.';
        header('Location: cart.php');
        exit;
    }

    if (empty($submit_cart_items)) {
        header('Location: cart.php');
        exit;
    }

    $total = 0;
    foreach ($submit_cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $total += 5; // flat shipping fee, matches cart.php's summary

    $order_query = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, status) VALUES (?, ?, ?, ?, 'pending')";
    $order_stmt = $conn->prepare($order_query);
    $order_stmt->bind_param("idss", $user['id'], $total, $address, $payment_method);

    if ($order_stmt->execute()) {
        $order_id = $order_stmt->insert_id;

        // Insert the order items and mark each product sold
        $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_query);

        $sold_query = "UPDATE products SET status = 'sold' WHERE id = ?";
        $sold_stmt = $conn->prepare($sold_query);

        foreach ($submit_cart_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];

            $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $item_stmt->execute();

            $sold_stmt->bind_param("i", $product_id);
            $sold_stmt->execute();
        }

        // Send confirmation emails (customer and admin); failures here don't block the order
        $subject = "Order Confirmation - ThriftX";
        $message = "Dear $name,\n\nThank you for your order! Here are your order details:\n\nOrder ID: $order_id\nTotal: ৳$total\n\nShipping Address: $address\n\nBest regards,\nThriftX Team";
        @mail($email, $subject, $message);

        $admin_email = "admin@thriftx.com";
        $admin_subject = "New Order Received - ThriftX";
        $admin_message = "A new order has been placed.\n\nOrder ID: $order_id\nCustomer Name: $name\nTotal: ৳$total\n\nShipping Address: $address\nCustomer Email: $email\n\nBest regards,\nThriftX Team";
        @mail($admin_email, $admin_subject, $admin_message);

        // Clear the real cart after successful checkout
        $cartManager->clearCart($user['id']);

        header('Location: thank_you.php');
        exit;
    } else {
        $error = "There was an error processing your order. Please try again.";
        $_SESSION['error'] = $error;
        header('Location: checkout.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - ThriftX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="customer-layout <?= (($_SESSION['theme'] ?? 'dark') === 'light') ? 'light-theme' : '' ?>">
    <?php include('../includes/customer_sidebar.php'); ?>

    <!-- Page Content -->
    <div class="page-content customer-page-content">
        <?php include('../includes/customer_header.php'); ?>

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-group">
                <a href="cart.php" class="page-back-btn" aria-label="Back to Cart">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12,19 5,12 12,5"></polyline>
                    </svg>
                </a>
                <div class="page-title">
                    <h1>Checkout</h1>
                    <p>Complete your purchase securely</p>
                </div>
            </div>
        </div>

        <!-- Checkout Section -->
        <div class="checkout-container">
            <div class="checkout-grid">
                <!-- Checkout Form -->
                <div class="checkout-form-section">
                    <h3>Shipping Information</h3>
                    
                    <!-- Display error message if any -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-error">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            <?= $_SESSION['error']; ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form action="checkout.php" method="POST" class="checkout-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" required placeholder="Enter your full name">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required placeholder="Enter your email address">
                        </div>

                        <div class="form-group">
                            <label for="address">Shipping Address *</label>
                            <textarea id="address" name="address" required placeholder="Enter your complete shipping address"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" class="form-select">
                                <option value="cash_on_delivery">Cash on Delivery</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary checkout-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 12l2 2 4-4"></path>
                                <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"></path>
                                <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"></path>
                            </svg>
                            Place Order
                        </button>
                    </form>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <h3>Order Summary</h3>
                    <div class="order-items">
                        <?php
                        $subtotal = 0;
                        foreach ($cart_items as $product):
                            $subtotal += $product['price'] * $product['quantity'];
                        ?>
                            <div class="order-item">
                                <div class="order-item-image">
                                    <img src="<?= !empty($product['image_url']) ? '../seller/' . htmlspecialchars($product['image_url']) : 'https://via.placeholder.com/60x60?text=Product'; ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                                </div>
                                <div class="order-item-info">
                                    <div class="order-item-name"><?= htmlspecialchars($product['name']); ?></div>
                                    <div class="order-item-details">
                                        <span class="order-item-price">৳<?= number_format($product['price'], 2); ?></span>
                                        <span class="order-item-quantity">× <?= $product['quantity']; ?></span>
                                    </div>
                                </div>
                                <div class="order-item-total">৳<?= number_format($product['price'] * $product['quantity'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-total">
                        <div class="total-line">
                            <span>Subtotal:</span>
                            <span>৳<?= number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="total-line">
                            <span>Shipping:</span>
                            <span>৳5.00</span>
                        </div>
                        <div class="total-line total-final">
                            <span>Total:</span>
                            <span>৳<?= number_format($subtotal + 5, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <footer class="footer">
            <p>&copy; 2025 ThriftX. All rights reserved.</p>
        </footer>
    </div> <!-- End page-content -->
</body>
</html>
