<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../index.php');
    exit;
}

include('../config/db.php');

// Initialize variables
$query = "";
$products = [];
$error_message = "";

// Get search query from URL parameter
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $query = trim($_GET['query']);
    
    // Search products in database
    $search_query = "%" . $query . "%";
    $sql = "SELECT * FROM products WHERE (name LIKE ? OR description LIKE ? OR category LIKE ?) AND status = 'active' ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search_query, $search_query, $search_query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
} else {
    $error_message = "Please enter a search query.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - ThriftX</title>
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
                    <h1>Search Results</h1>
                    <p>Search results for "<?= htmlspecialchars($query); ?>"</p>
                </div>
            </div>
        </div>

        <!-- Search Results Section -->
        <div class="search-results-container">
            <?php if (!empty($error_message)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </div>
                    <h3>Search Required</h3>
                    <p><?= $error_message; ?></p>
                    <a href="dashboard.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php elseif (empty($products)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </div>
                    <h3>No Results Found</h3>
                    <p>Sorry, we couldn't find any products matching "<?= htmlspecialchars($query); ?>".</p>
                    <p>Try searching with different keywords or browse our categories.</p>
                    <a href="dashboard.php" class="btn btn-primary">Browse All Products</a>
                </div>
            <?php else: ?>
                <div class="search-info">
                    <p>Found <?= count($products); ?> product<?= count($products) !== 1 ? 's' : ''; ?> for "<?= htmlspecialchars($query); ?>"</p>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?= !empty($product['image_url']) ? htmlspecialchars('../seller/' . $product['image_url']) : 'https://via.placeholder.com/300x200?text=Product'; ?>"
                                     alt="<?= htmlspecialchars($product['name']); ?>">
                                <div class="product-category"><?= ucfirst($product['category']); ?></div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?= htmlspecialchars($product['name']); ?></h3>
                                <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : ''); ?></p>
                                <div class="product-price">$<?= number_format($product['price'], 2); ?></div>
                                <div class="product-actions">
                                    <a href="product_page.php?id=<?= $product['id']; ?>" class="btn btn-primary">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
