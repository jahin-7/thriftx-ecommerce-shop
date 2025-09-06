<?php
require_once('../includes/auth.php');
requireLogin();

include('../config/db.php');

// Get current user info
$user = getCurrentUser();

// Fetch featured products for display
$query = "SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 8";
$products_result = $conn->query($query);

// Fetch categories for sidebar
$categories_query = "SELECT DISTINCT category FROM products WHERE status = 'active' ORDER BY category";
$categories_result = $conn->query($categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ThriftX - Your Thrift Store</title>
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
            <a href="dashboard.php" class="nav-item active">
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

        <!-- Header -->
        <?php include('../includes/customer_header.php'); ?>
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1>Welcome to ThriftX</h1>
                <p>Hello, <?= htmlspecialchars($user['first_name']) ?>! Discover amazing deals today</p>
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

    <section class="categories">
        <h2>Popular Categories</h2>
        <div class="category-list">
            <button class="button" onclick="window.location.href='electronics.php'">Electronics</button>
            <button class="button" onclick="window.location.href='clothing.php'">Clothing</button>
            <button class="button" onclick="window.location.href='furniture.php'">Furniture</button>
            <button class="button" onclick="window.location.href='services.php'">Services</button>
        </div>
    </section>

    <section class="products">
        <h2>Featured Products</h2>
        <div class="product-list">
            <?php if ($products_result && $products_result->num_rows > 0): ?>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <div class="product-item">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'https://via.placeholder.com/200x150?text=No+Image'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <p><?php echo htmlspecialchars($product['name']); ?></p>
                        <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : ''); ?></p>
                        <p>৳ <?php echo number_format($product['price'], 2); ?></p>
                        <div class="product-buttons">
                            <a href="product_page.php?id=<?php echo $product['id']; ?>" class="view-product-btn">View Details</a>
                            <form action="cart.php" method="POST" style="display: contents;">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                                <input type="hidden" name="name" value="<?= htmlspecialchars($product['name']); ?>">
                                <input type="hidden" name="price" value="<?= $product['price']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-products">
                    <h3>No products available at the moment</h3>
                    <p>Check back later for new items!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

        <footer class="footer">
            <p>&copy; 2025 ThriftX. All rights reserved.</p>
        </footer>
    </div> <!-- End page-content -->
</body>
</html>
