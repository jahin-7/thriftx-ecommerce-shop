<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('../config/db.php');
require_once('../includes/cart_functions.php');

// Check if user is logged in and is customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../index.php');
    exit;
}

// Get user info
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get cart count from database
$cart_count = $cartManager->getCartCount($user_id);
?>


<header class="customer-header">
    <div class="header-left">
        <div class="logo-section">
            <a href="dashboard.php" class="logo-link">
                <div class="logo-icon">T</div>
                <span class="logo-text">ThriftX</span>
            </a>
        </div>
    </div>
    
    <div class="header-right">
        <div class="header-actions">
            <!-- Cart -->
            <div class="cart-section">
                <a href="cart.php" class="cart-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span>Cart</span>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- User Menu - Simple Links -->
            <div class="user-menu-simple">
                <a href="profile_settings.php" class="user-link">
                    <div class="user-avatar">
                        <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                    </div>
                    <span class="user-name"><?= htmlspecialchars($user['first_name']) ?></span>
                </a>
                <a href="../logout.php" class="logout-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16,17 21,12 16,7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</header>

<!-- No JavaScript - Pure HTML/CSS/PHP -->
