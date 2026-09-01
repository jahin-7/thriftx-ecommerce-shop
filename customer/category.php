<?php
require_once('../includes/auth.php');
requireLogin();

include('../config/db.php');

$categories = [
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

$category_descriptions = [
    'electronics' => 'Great deals on gently used electronics',
    'clothing' => 'Find stylish and affordable clothing items',
    'furniture' => 'Quality furniture for every room',
    'services' => 'Professional services for all your needs',
    'books' => 'Pre-loved books at great prices',
    'sports' => 'Gear up for your next adventure',
    'home_garden' => 'Everything for your home and garden',
    'beauty_health' => 'Beauty and health essentials',
    'toys_games' => 'Toys and games for all ages',
    'other' => 'Unique finds that do not fit elsewhere',
];

$cat = isset($_GET['cat']) ? $_GET['cat'] : '';

if (!isset($categories[$cat])) {
    header('Location: dashboard.php');
    exit;
}

$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'lowToHigh';
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : null;
$price_filter_active = $max_price !== null && $max_price < 10000;

$order_clause = $sort_by == 'highToLow' ? 'ORDER BY price DESC' : 'ORDER BY price ASC';

if ($price_filter_active) {
    $query = "SELECT * FROM products WHERE category = ? AND price <= ? $order_clause";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $cat, $max_price);
} else {
    $query = "SELECT * FROM products WHERE category = ? $order_clause";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $cat);
}

$stmt->execute();
$products_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($categories[$cat]) ?> - ThriftX</title>
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
                <a href="dashboard.php" class="page-back-btn" aria-label="Back to Dashboard">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12,19 5,12 12,5"></polyline>
                    </svg>
                </a>
                <div class="page-title">
                    <h1><?= htmlspecialchars($categories[$cat]) ?></h1>
                    <p><?= htmlspecialchars($category_descriptions[$cat]) ?></p>
                </div>
            </div>
        </div>

        <section class="search">
            <div class="search-container">
                <form action="search_results.php" method="GET">
                    <input type="text" class="search-bar" name="query" placeholder="Search for anything..." required>
                    <button type="submit" class="search-button">Search</button>
                </form>
            </div>
        </section>

        <section class="sort-filter">
            <label for="sortBy">Sort By:</label>
            <select id="sortBy" onchange="window.location.href='category.php?cat=<?= urlencode($cat) ?><?= $price_filter_active ? '&max_price=' . $max_price : '' ?>&sort_by=' + this.value">
                <option value="lowToHigh" <?= $sort_by == 'lowToHigh' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="highToLow" <?= $sort_by == 'highToLow' ? 'selected' : '' ?>>Price: High to Low</option>
            </select>
            <?php if ($price_filter_active): ?>
                <span class="active-price-filter">
                    Up to ৳<?= number_format($max_price) ?>
                    <a href="category.php?cat=<?= urlencode($cat) ?>&sort_by=<?= urlencode($sort_by) ?>" class="clear-price-filter" aria-label="Clear price filter">&times;</a>
                </span>
            <?php endif; ?>
        </section>

        <section class="products">
            <h2><?= htmlspecialchars($categories[$cat]) ?> Products</h2>
            <div class="product-list">
                <?php if ($products_result->num_rows === 0): ?>
                    <div class="no-products">
                        <h3>No products in this category yet</h3>
                        <p>Check back later for new items!</p>
                    </div>
                <?php endif; ?>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <?php $is_available = $product['status'] === 'active'; ?>
                    <div class="product-item<?= $is_available ? '' : ' product-item--unavailable' ?>">
                        <?php if (!$is_available): ?>
                            <span class="stock-badge stock-badge--<?= $product['status'] === 'sold' ? 'sold' : 'inactive' ?>">
                                <?= $product['status'] === 'sold' ? 'Sold' : 'Unavailable' ?>
                            </span>
                        <?php endif; ?>
                        <img src="<?= !empty($product['image_url']) ? '../seller/' . htmlspecialchars($product['image_url']) : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                        <p><?= htmlspecialchars($product['name']); ?></p>
                        <p>৳ <?= number_format($product['price'], 2); ?></p>
                        <?php if ($is_available): ?>
                            <form action="cart.php" method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <button type="button" class="add-to-cart-btn" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <footer class="footer">
            <p>&copy; 2025 ThriftX. All rights reserved.</p>
        </footer>
    </div> <!-- End page-content -->
</body>
</html>
