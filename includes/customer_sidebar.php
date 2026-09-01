<?php
// Shared customer sidebar. Requires $conn (config/db.php) to already be included.
// This must run before any output, since it may redirect (invalid/foreign-role session).
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../index.php');
    exit;
}

// Categories are the canonical site-wide list, also used by seller/admin product forms.
$__categories = [
    'electronics' => 'Electronics',
    'clothing' => 'Clothing',
    'furniture' => 'Furniture',
    'services' => 'Services',
    'books' => 'Books',
    'sports' => 'Sports & Outdoors',
    'home_garden' => 'Home & Garden',
    'beauty_health' => 'Beauty & Health',
    'toys_games' => 'Toys & Games',
    'other' => 'Other',
];

$__current_page = basename($_SERVER['PHP_SELF']);
$__current_cat = isset($_GET['cat']) ? $_GET['cat'] : '';
$__filter_cat = isset($__categories[$__current_cat]) ? $__current_cat : 'electronics';
$__max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 10000;
?>
<!-- Sidebar Toggle -->
<input id="sidebar-toggle" type="checkbox" checked>
<label class="sidebar-hamburger" for="sidebar-toggle">
    <div class="bars"></div>
    <div class="bars"></div>
    <div class="bars"></div>
</label>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay"></div>

<!-- Sidebar -->
<div class="sidebar">
    <nav class="sidebar-nav">

        <!-- Price filter -->
        <div class="sidebar-filter-box">
            <p class="sidebar-section-label">Filter by Price</p>
            <form method="GET" action="category.php" class="price-filter-form">
                <input type="hidden" name="cat" value="<?= htmlspecialchars($__filter_cat) ?>">
                <div class="price-range-track">
                    <span>৳0</span>
                    <span>৳10,000+</span>
                </div>
                <input type="range" name="max_price" min="0" max="10000" step="100" value="<?= $__max_price ?>" class="price-range-slider">
                <button type="submit" class="price-filter-apply">Apply</button>
            </form>
        </div>

        <!-- Collapsible categories -->
        <input type="checkbox" id="categories-collapse-toggle" class="categories-collapse-toggle" checked>
        <label for="categories-collapse-toggle" class="sidebar-section-toggle">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
            <span>Categories</span>
            <svg class="collapse-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6,9 12,15 18,9"></polyline>
            </svg>
        </label>

        <div class="sidebar-categories-collapse">
            <?php foreach ($__categories as $__slug => $__label): ?>
                <a href="category.php?cat=<?= urlencode($__slug) ?>" class="nav-item nav-item--category <?= ($__current_page === 'category.php' && $__current_cat === $__slug) ? 'active' : '' ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span><?= htmlspecialchars($__label) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="sidebar-divider"></div>

        <a href="profile_settings.php" class="nav-item <?= $__current_page === 'profile_settings.php' ? 'active' : '' ?>">
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
