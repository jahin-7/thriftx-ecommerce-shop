<?php
require_once('../includes/auth.php');
requireLogin();

include('../config/db.php');

// Get current user info
$user = getCurrentUser();

// Default sort option is "Low to High"
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'lowToHigh';

if ($sort_by == 'lowToHigh') {
    $query = "SELECT * FROM products WHERE category = 'electronics' AND status = 'active' ORDER BY price ASC";
} else if ($sort_by == 'highToLow') {
    $query = "SELECT * FROM products WHERE category = 'electronics' AND status = 'active' ORDER BY price DESC";
} else {
    $query = "SELECT * FROM products WHERE category = 'electronics' AND status = 'active' ORDER BY created_at DESC"; 
}

$electronics_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electronics - ThriftX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script>
        function checkProductAvailability(categoryOrProduct) { 
            const availableItems = ['Clothing', 'Electronics', 'Furniture', 'Product 1', 'Product 2'];
            if (!availableItems.includes(categoryOrProduct)) {
                window.location.href = '../includes/not-available.php';
            } else {
                alert(categoryOrProduct + ' is available!');
            }
        }
    </script>
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
        <h3>Categories</h3>
        <a href="electronics.php"><button>Electronics</button></a>
        <a href="clothing.php"><button>Clothing</button></a>
        <a href="furniture.php"><button>Furniture</button></a>
        <a href="services.php"><button>Services</button></a>
    </div>

    <!-- Page Content -->
    <div class="page-content customer-page-content">

    <?php include('../includes/customer_header.php'); ?>
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <h1>Electronics</h1>
            <p>Discover the latest electronic gadgets and devices</p>
        </div>
        <div class="page-actions">
            <a href="cart.php" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                View Cart
            </a>
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
        <select id="sortBy" onchange="window.location.href='electronics.php?sort_by=' + this.value">
            <option value="lowToHigh" <?php echo ($sort_by == 'lowToHigh') ? 'selected' : ''; ?>>Price: Low to High</option>
            <option value="highToLow" <?php echo ($sort_by == 'highToLow') ? 'selected' : ''; ?>>Price: High to Low</option>
        </select>
    </section>

    <section class="products">
        <h2>Electronics Products</h2>
        <div class="product-list">
            <?php while ($product = $electronics_result->fetch_assoc()): ?>
                <div class="product-item">
                    <img src="<?= $product['image_url']; ?>" alt="<?= $product['name']; ?>">
                    <p><?= $product['name']; ?></p>
                    <p>৳ <?= number_format($product['price'], 2); ?></p>
                    <form action="cart.php" method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($product['name']); ?>">
                        <input type="hidden" name="price" value="<?= $product['price']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                    </form>
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
