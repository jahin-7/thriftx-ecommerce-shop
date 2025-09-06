<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Handle search and filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= "ss";
}

if (!empty($category)) {
    $where_conditions[] = "category = ?";
    $params[] = $category;
    $param_types .= "s";
}

if (!empty($status)) {
    $where_conditions[] = "status = ?";
    $params[] = $status;
    $param_types .= "s";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM products $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

// Fetch products with seller info
$query = "SELECT p.*, u.first_name, u.last_name 
          FROM products p 
          LEFT JOIN users u ON p.seller_id = u.id 
          $where_clause 
          ORDER BY p.created_at DESC 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $all_params = array_merge($params, [$limit, $offset]);
    $stmt->bind_param($param_types . "ii", ...$all_params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// Get categories for filter
$categories_query = "SELECT DISTINCT category FROM products ORDER BY category";
$categories_result = $conn->query($categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Products - ThriftX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="checkout-section">
            <div class="admin-page-header">
                <h2>Product Management</h2>
                <a href="admin_add_product.php" class="add-product-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add New Product
                </a>
            </div>

            <!-- Search and Filters -->
            <div class="admin-filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <input type="text" name="search" placeholder="Search products..." 
                               value="<?= htmlspecialchars($search) ?>" class="search-input">
                    </div>
                    <div class="filter-group">
                        <select name="category" class="filter-select">
                            <option value="">All Categories</option>
                            <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                <option value="<?= $cat['category'] ?>" 
                                        <?= $category === $cat['category'] ? 'selected' : '' ?>>
                                    <?= ucfirst($cat['category']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select name="status" class="filter-select">
                            <option value="">All Status</option>
                            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="sold" <?= $status === 'sold' ? 'selected' : '' ?>>Sold</option>
                        </select>
                    </div>
                    <button type="submit" class="filter-btn">Filter</button>
                    <a href="admin_products.php" class="clear-btn">Clear</a>
                </form>
            </div>

            <!-- Products Grid -->
            <div class="admin-products-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <div class="admin-product-card">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?= htmlspecialchars($product['name']); ?>"
                                     onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                                <div class="product-status status-<?= $product['status'] ?>">
                                    <?= ucfirst($product['status']) ?>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-price">৳ <?= number_format($product['price'], 2) ?></p>
                                <p class="product-category"><?= ucfirst($product['category']) ?></p>
                                <p class="product-seller">
                                    Seller: <?= htmlspecialchars($product['first_name'] . ' ' . $product['last_name']) ?>
                                </p>
                                <p class="product-description">
                                    <?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...
                                </p>
                                <div class="product-actions">
                                    <a href="admin_edit_product.php?id=<?= $product['id'] ?>" class="edit-btn">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                        Edit
                                    </a>
                                    <a href="admin_delete_product.php?id=<?= $product['id'] ?>" 
                                       class="delete-btn" 
                                       onclick="return confirm('Are you sure you want to delete this product?')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3,6 5,6 21,6"></polyline>
                                            <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                                        </svg>
                                        Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-products">
                        <h3>No products found</h3>
                        <p>Try adjusting your search criteria or add a new product.</p>
                        <a href="admin_add_product.php" class="add-product-btn">Add First Product</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="admin-pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($category) ? '&category=' . urlencode($category) : '' ?><?= !empty($status) ? '&status=' . urlencode($status) : '' ?>" class="page-btn">First</a>
                        <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($category) ? '&category=' . urlencode($category) : '' ?><?= !empty($status) ? '&status=' . urlencode($status) : '' ?>" class="page-btn">Prev</a>
                    <?php endif; ?>
                    
                    <span class="page-info">Page <?= $page ?> of <?= $total_pages ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($category) ? '&category=' . urlencode($category) : '' ?><?= !empty($status) ? '&status=' . urlencode($status) : '' ?>" class="page-btn">Next</a>
                        <a href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($category) ? '&category=' . urlencode($category) : '' ?><?= !empty($status) ? '&status=' . urlencode($status) : '' ?>" class="page-btn">Last</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
