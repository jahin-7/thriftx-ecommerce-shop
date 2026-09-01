<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../index.php');
    exit;
}

$seller_id = $_SESSION['user_id'];
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where_conditions = ["p.seller_id = ?"];
$params = [$seller_id];
$param_types = "i";

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Orders that contain at least one of this seller's products.
// total_amount reflects the whole order (may include other sellers' items);
// "your_items_total" is this seller's share of that order.
$query = "SELECT o.id, o.status, o.created_at, o.total_amount,
                 CONCAT(u.first_name, ' ', u.last_name) AS customer_name, u.email AS customer_email,
                 SUM(oi.price * oi.quantity) AS your_items_total,
                 GROUP_CONCAT(DISTINCT pr.name SEPARATOR ', ') AS your_products
          FROM orders o
          JOIN order_items oi ON oi.order_id = o.id
          JOIN products p ON p.id = oi.product_id
          JOIN products pr ON pr.id = oi.product_id
          JOIN users u ON u.id = o.user_id
          $where_clause
          GROUP BY o.id
          ORDER BY o.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - ThriftX Seller</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="<?= (($_SESSION['theme'] ?? 'dark') === 'light') ? 'light-theme' : '' ?>">
    <!-- Facebook-style Seller Header -->
    <?php include('../includes/seller_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="page-header">
            <div class="page-title">
                <h1>My Orders</h1>
                <p>Orders that include products you have listed</p>
            </div>
        </div>

        <div class="checkout-section">
            <div class="admin-filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <select name="status" class="filter-select">
                            <option value="">All Status</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="filter-btn">Filter</button>
                    <a href="seller_orders.php" class="clear-btn">Clear</a>
                </form>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="admin-products-grid">
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
                                <p class="order-total">Your items: ৳ <?= number_format($order['your_items_total'], 2) ?></p>
                                <p class="order-date"><?= htmlspecialchars($order['your_products']) ?></p>
                                <p class="order-date">Date: <?= date('M d, Y', strtotime($order['created_at'])) ?></p>

                                <div class="status-update">
                                    <form action="../admin/update_order.php" method="POST" class="status-form">
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
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-products">
                    <h3>No orders yet</h3>
                    <p>Orders containing your products will show up here.</p>
                </div>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <p>&copy; 2025 ThriftX. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
