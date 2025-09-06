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
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= "ss";
}

if (!empty($status)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status;
    $param_types .= "s";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.id $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_orders = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $limit);

// Fetch orders with customer info
$query = "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name, u.email as customer_email
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          $where_clause 
          ORDER BY o.created_at DESC 
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders - ThriftX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="checkout-section">
            <div class="admin-page-header">
                <h2>Order Management</h2>
            </div>

            <!-- Search and Filters -->
            <div class="admin-filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <input type="text" name="search" placeholder="Search by customer name or email..." 
                               value="<?= htmlspecialchars($search) ?>" class="search-input">
                    </div>
                    <div class="filter-group">
                        <select name="status" class="filter-select">
                            <option value="">All Status</option>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="filter-btn">Filter</button>
                    <a href="admin_orders.php" class="clear-btn">Clear</a>
                </form>
            </div>

            <!-- Orders Grid -->
            <div class="admin-products-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($order = $result->fetch_assoc()): ?>
                        <div class="admin-product-card">
                            <div class="order-header">
                                <div class="order-id">Order #<?= $order['id'] ?></div>
                                <div class="order-status status-<?= $order['status'] ?>">
                                    <?= ucfirst($order['status']) ?>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($order['customer_name']) ?></h3>
                                <p class="customer-email"><?= htmlspecialchars($order['customer_email']) ?></p>
                                <p class="order-total">Total: ৳ <?= number_format($order['total_amount'], 2) ?></p>
                                <p class="order-date">Date: <?= date('M d, Y', strtotime($order['created_at'])) ?></p>
                                
                                <div class="status-update">
                                    <form action="update_order.php" method="POST" class="status-form">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" onchange="this.form.submit()" class="status-select">
                                            <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                            <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                            <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </div>
                                
                                <div class="product-actions">
                                    <a href="order_details.php?id=<?= $order['id'] ?>" class="edit-btn">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-products">
                        <h3>No orders found</h3>
                        <p>Try adjusting your search criteria.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="admin-pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($status) ? '&status=' . urlencode($status) : '' ?>" class="page-btn">First</a>
                        <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($status) ? '&status=' . urlencode($status) : '' ?>" class="page-btn">Prev</a>
                    <?php endif; ?>
                    
                    <span class="page-info">Page <?= $page ?> of <?= $total_pages ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($status) ? '&status=' . urlencode($status) : '' ?>" class="page-btn">Next</a>
                        <a href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($status) ? '&status=' . urlencode($status) : '' ?>" class="page-btn">Last</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
