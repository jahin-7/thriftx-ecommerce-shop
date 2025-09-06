<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Get notification count (example - you can customize this)
$notification_count = 0;
if (isset($conn)) {
    $notif_query = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
    $notif_result = $conn->query($notif_query);
    if ($notif_result) {
        $notification_count = $notif_result->fetch_assoc()['count'];
    }
}
?>

<!-- Facebook-style Admin Header -->
<header class="admin-header">
    <div class="header-left">
        <div class="logo-section">
            <a href="admin_dashboard.php" class="logo-link">
                <div class="logo-icon">T</div>
                <span class="logo-text">ThriftX Admin</span>
            </a>
        </div>
        
        <div class="search-section">
            <div class="search-box">
                <input type="text" placeholder="Search products, orders, users..." class="search-input">
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
            <a href="admin_dashboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9,22 9,12 15,12 15,22"></polyline>
                </svg>
                <span>Dashboard</span>
            </a>
            
            <a href="admin_products.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'admin_products.php' ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <span>Products</span>
            </a>
            
            <a href="admin_orders.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'admin_orders.php' ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                </svg>
                <span>Orders</span>
                <?php if ($notification_count > 0): ?>
                    <span class="notification-badge"><?= $notification_count ?></span>
                <?php endif; ?>
            </a>
            
            <a href="admin_users.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Users</span>
            </a>
            
            <a href="admin_analytics.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'admin_analytics.php' ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                <span>Analytics</span>
            </a>
        </nav>
    </div>

    <div class="header-right">
        <div class="header-actions">
            <!-- Notifications -->
            <div class="notification-dropdown">
                <button class="notification-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <?php if ($notification_count > 0): ?>
                        <span class="notification-badge"><?= $notification_count ?></span>
                    <?php endif; ?>
                </button>
                <div class="notification-menu">
                    <div class="notification-header">
                        <h4>Notifications</h4>
                        <button class="mark-all-read">Mark all as read</button>
                    </div>
                    <div class="notification-list">
                        <?php if ($notification_count > 0): ?>
                            <div class="notification-item">
                                <div class="notification-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                    </svg>
                                </div>
                                <div class="notification-content">
                                    <p><?= $notification_count ?> pending orders need your attention</p>
                                    <span class="notification-time">Just now</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="notification-item">
                                <p>No new notifications</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

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
                        <a href="admin_profile.php" class="menu-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span>Profile</span>
                        </a>
                        <a href="admin_settings.php" class="menu-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                            <span>Settings</span>
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
    // Notification dropdown
    const notificationBtn = document.querySelector('.notification-btn');
    const notificationMenu = document.querySelector('.notification-menu');
    
    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationMenu.classList.toggle('show');
        userMenu.classList.remove('show');
    });

    // User dropdown
    const userBtn = document.querySelector('.user-btn');
    const userMenu = document.querySelector('.user-menu');
    
    userBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userMenu.classList.toggle('show');
        notificationMenu.classList.remove('show');
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        notificationMenu.classList.remove('show');
        userMenu.classList.remove('show');
    });

    // Search functionality
    const searchInput = document.querySelector('.search-input');
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query) {
                // Implement search functionality
                window.location.href = `admin_search.php?q=${encodeURIComponent(query)}`;
            }
        }
    });
});
</script>
