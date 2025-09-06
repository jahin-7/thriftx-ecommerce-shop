<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// If the cart is empty, redirect to the cart page
if (empty($_SESSION['cart'])) {
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

    // Calculate the total price of the cart
    $total = 0;
    foreach ($_SESSION['cart'] as $product) {
        $total += $product['price'] * $product['quantity'];
    }

    // Insert the order into the database
    include('../config/db.php');  // Database connection
    $query = "INSERT INTO orders (name, email, address, phone, total) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $name, $email, $address, $phone, $total);
    
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id; // Get the last inserted order ID

        // Insert the order items into the order_items table
        foreach ($_SESSION['cart'] as $product_id => $product) {
            $quantity = $product['quantity'];
            $price = $product['price'];

            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_query);
            $item_stmt->bind_param("iiii", $order_id, $product_id, $quantity, $price);
            $item_stmt->execute();
        }

        // Send confirmation emails (customer and admin)
        $subject = "Order Confirmation - ThriftX";
        $message = "Dear $name,\n\nThank you for your order! Here are your order details:\n\nOrder ID: $order_id\nTotal: ৳$total\n\nShipping Address: $address\n\nBest regards,\nThriftX Team";
        mail($email, $subject, $message);

        // Admin email notification
        $admin_email = "admin@thriftx.com";  // Admin email
        $admin_subject = "New Order Received - ThriftX";
        $admin_message = "A new order has been placed.\n\nOrder ID: $order_id\nCustomer Name: $name\nTotal: ৳$total\n\nShipping Address: $address\nCustomer Email: $email\n\nBest regards,\nThriftX Team";
        mail($admin_email, $admin_subject, $admin_message);

        // Clear the cart after successful checkout
        unset($_SESSION['cart']);

        // Redirect to the thank you page
        header('Location: thank_you.php');
        exit;
    } else {
        // If the order insertion fails, return an error
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
        <div class="sidebar-header">
            <div class="logo-section">
                <div class="logo-icon">T</div>
                <span class="logo-text">ThriftX</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="cart.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span>Shopping Cart</span>
            </a>
            <a href="electronics.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
                <span>Electronics</span>
            </a>
            <a href="clothing.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.38 3.46L16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"></path>
                </svg>
                <span>Clothing</span>
            </a>
            <a href="furniture.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"></path>
                    <path d="M8 21V8a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v13"></path>
                </svg>
                <span>Furniture</span>
            </a>
            <a href="services.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Services</span>
            </a>
            <a href="profile_settings.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Profile & Settings</span>
            </a>
            <a href="../logout.php" class="nav-item logout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16,17 21,12 16,7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <!-- Page Content -->
    <div class="page-content customer-page-content">
        <?php include('../includes/customer_header.php'); ?>
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1>Checkout</h1>
                <p>Complete your purchase securely</p>
            </div>
            <div class="page-actions">
                <a href="cart.php" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    Back to Cart
                </a>
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
                        foreach ($_SESSION['cart'] as $product): 
                            $subtotal += $product['price'] * $product['quantity'];
                        ?>
                            <div class="order-item">
                                <div class="order-item-image">
                                    <img src="https://via.placeholder.com/60x60?text=Product" alt="<?= htmlspecialchars($product['name']); ?>">
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
