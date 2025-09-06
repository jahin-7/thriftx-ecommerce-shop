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

// Get analytics data
$analytics = [];

// Total users by role
$users_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$users_result = $conn->query($users_query);
$analytics['users_by_role'] = [];
while ($row = $users_result->fetch_assoc()) {
    $analytics['users_by_role'][$row['role']] = $row['count'];
}

// Total products by category
$products_query = "SELECT category, COUNT(*) as count FROM products GROUP BY category";
$products_result = $conn->query($products_query);
$analytics['products_by_category'] = [];
while ($row = $products_result->fetch_assoc()) {
    $analytics['products_by_category'][$row['category']] = $row['count'];
}

// Monthly sales (last 6 months)
$sales_query = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as order_count,
    SUM(total_amount) as total_sales
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC";
$sales_result = $conn->query($sales_query);
$analytics['monthly_sales'] = [];
while ($row = $sales_result->fetch_assoc()) {
    $analytics['monthly_sales'][] = $row;
}

// Top selling products
$top_products_query = "SELECT 
    p.name, 
    p.category, 
    p.price,
    COUNT(oi.id) as times_ordered,
    SUM(oi.quantity) as total_quantity
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    GROUP BY p.id
    ORDER BY times_ordered DESC, total_quantity DESC
    LIMIT 10";
$top_products_result = $conn->query($top_products_query);
$analytics['top_products'] = [];
while ($row = $top_products_result->fetch_assoc()) {
    $analytics['top_products'][] = $row;
}

// Recent activity
$recent_orders_query = "SELECT 
    o.id, 
    o.total_amount, 
    o.status, 
    o.created_at,
    CONCAT(u.first_name, ' ', u.last_name) as customer_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 10";
$recent_orders_result = $conn->query($recent_orders_query);
$analytics['recent_orders'] = [];
while ($row = $recent_orders_result->fetch_assoc()) {
    $analytics['recent_orders'][] = $row;
}

// Overall stats
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM products) as total_products,
    (SELECT COUNT(*) FROM orders) as total_orders,
    (SELECT SUM(total_amount) FROM orders WHERE status = 'delivered') as total_revenue";
$stats_result = $conn->query($stats_query);
$analytics['overall_stats'] = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Analytics - ThriftX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="checkout-section">
            <div class="admin-page-header">
                <h2>Analytics & Reports</h2>
                <div class="analytics-date-range">
                    <span>Last 6 months</span>
                </div>
            </div>

            <!-- Overall Stats -->
            <div class="stats-grid">
                <div class="stat-box">
                    <h3>Total Users</h3>
                    <p class="stat-value"><?= number_format($analytics['overall_stats']['total_users']) ?></p>
                </div>
                <div class="stat-box">
                    <h3>Total Products</h3>
                    <p class="stat-value"><?= number_format($analytics['overall_stats']['total_products']) ?></p>
                </div>
                <div class="stat-box">
                    <h3>Total Orders</h3>
                    <p class="stat-value"><?= number_format($analytics['overall_stats']['total_orders']) ?></p>
                </div>
                <div class="stat-box">
                    <h3>Total Revenue</h3>
                    <p class="stat-value">৳ <?= number_format($analytics['overall_stats']['total_revenue'] ?? 0, 2) ?></p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="analytics-charts">
                <div class="chart-container">
                    <h3>Users by Role</h3>
                    <div class="chart-data">
                        <?php foreach ($analytics['users_by_role'] as $role => $count): ?>
                            <div class="chart-item">
                                <span class="chart-label"><?= ucfirst($role) ?></span>
                                <div class="chart-bar">
                                    <div class="chart-fill" style="width: <?= ($count / max($analytics['users_by_role'])) * 100 ?>%"></div>
                                </div>
                                <span class="chart-value"><?= $count ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="chart-container">
                    <h3>Products by Category</h3>
                    <div class="chart-data">
                        <?php foreach ($analytics['products_by_category'] as $category => $count): ?>
                            <div class="chart-item">
                                <span class="chart-label"><?= ucfirst($category) ?></span>
                                <div class="chart-bar">
                                    <div class="chart-fill" style="width: <?= ($count / max($analytics['products_by_category'])) * 100 ?>%"></div>
                                </div>
                                <span class="chart-value"><?= $count ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Monthly Sales -->
            <div class="analytics-section">
                <h3>Monthly Sales (Last 6 Months)</h3>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($analytics['monthly_sales'] as $month): ?>
                                <tr>
                                    <td><?= date('F Y', strtotime($month['month'] . '-01')) ?></td>
                                    <td><?= $month['order_count'] ?></td>
                                    <td>৳ <?= number_format($month['total_sales'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Products -->
            <div class="analytics-section">
                <h3>Top Selling Products</h3>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Times Ordered</th>
                                <th>Total Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($analytics['top_products'] as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= ucfirst($product['category']) ?></td>
                                    <td>৳ <?= number_format($product['price'], 2) ?></td>
                                    <td><?= $product['times_ordered'] ?></td>
                                    <td><?= $product['total_quantity'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="analytics-section">
                <h3>Recent Orders</h3>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($analytics['recent_orders'] as $order): ?>
                                <tr>
                                    <td><?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td>৳ <?= number_format($order['total_amount'], 2) ?></td>
                                    <td><span class="status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                    <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
