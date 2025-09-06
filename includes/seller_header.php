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
        
        <div class="search-section">
            <div class="search-box">
                <input type="text" placeholder="Search your products..." class="search-input">
                <button class="search-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
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
        </nav>
    </div>

    <div class="header-right">
        <div class="header-actions">
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

    // Search functionality
    const searchInput = document.querySelector('.search-input');
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query) {
                // Implement search functionality
                window.location.href = `seller_products.php?search=${encodeURIComponent(query)}`;
            }
        }
    });
});
</script>
