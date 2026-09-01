<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../index.php');
    exit;
}
?>

<!-- Facebook-style Seller Header -->
<header class="seller-header">
    <div class="header-left">
        <div class="logo-section">
            <a href="seller_dashboard.php" class="logo-link">
                <div class="logo-icon">T</div>
                <span class="logo-text">ThriftX Seller</span>
            </a>
        </div>
    </div>

    <div class="header-center">
        <nav class="main-nav">
            <a href="seller_dashboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'seller_dashboard.php' ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>Dashboard</span>
            </a>
            
            <a href="post_product.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'post_product.php' ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Post Product</span>
            </a>
            
            <a href="seller_products.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'seller_products.php' ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27,6.96 12,12.01 20.73,6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
                <span>My Products</span>
            </a>

            <a href="seller_orders.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'seller_orders.php' ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                </svg>
                <span>Orders</span>
            </a>
        </nav>
    </div>

    <div class="header-right">
        <div class="header-actions">
            <a href="../theme_toggle.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="theme-toggle-btn" aria-label="Toggle light/dark theme" title="Toggle theme">
                <?php if (($_SESSION['theme'] ?? 'dark') === 'light'): ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                <?php else: ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                <?php endif; ?>
            </a>

            <a href="../logout.php" class="header-logout-btn" aria-label="Logout" title="Logout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16,17 21,12 16,7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Logout</span>
            </a>

            <!-- User Menu -->
            <div class="user-dropdown">
                <button class="user-btn">
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['first_name'], 0, 1)) ?>
                    </div>
                    <span class="user-name"><?= htmlspecialchars($_SESSION['first_name']) ?></span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6,9 12,15 18,9"></polyline>
                    </svg>
                </button>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-avatar-large">
                            <?= strtoupper(substr($_SESSION['first_name'], 0, 1)) ?>
                        </div>
                        <div class="user-details">
                            <h4><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></h4>
                            <p><?= htmlspecialchars($_SESSION['email']) ?></p>
                        </div>
                    </div>
                    <div class="user-menu-items">
                        <a href="profile_settings.php" class="menu-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span>Profile</span>
                        </a>
                        <a href="seller_products.php" class="menu-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <span>My Products</span>
                        </a>
                        <div class="menu-divider"></div>
                        <a href="../logout.php" class="menu-item logout">
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
        </div>
    </div>
</header>

<script>
// Toggle dropdowns
document.addEventListener('DOMContentLoaded', function() {
    // User dropdown
    const userBtn = document.querySelector('.user-btn');
    const userMenu = document.querySelector('.user-menu');
    
    userBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userMenu.classList.toggle('show');
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        userMenu.classList.remove('show');
    });
});
</script>
