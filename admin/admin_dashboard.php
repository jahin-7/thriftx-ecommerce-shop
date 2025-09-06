<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');  // Include database connection

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Fetch total sales
$sales_query = "SELECT SUM(total_amount) AS total_sales FROM orders WHERE status = 'delivered'";
$sales_result = $conn->query($sales_query);
if (!$sales_result) {
    die("Error fetching total sales: " . $conn->error);
}
$total_sales = $sales_result->fetch_assoc()['total_sales'] ?? 0;

// Fetch total products
$products_query = "SELECT COUNT(*) AS total_products FROM products";
$products_result = $conn->query($products_query);
if (!$products_result) {
    die("Error fetching total products: " . $conn->error);
}
$total_products = $products_result->fetch_assoc()['total_products'];

// Fetch pending orders
$pending_orders_query = "SELECT COUNT(*) AS pending_orders FROM orders WHERE status = 'pending'";
$pending_orders_result = $conn->query($pending_orders_query);
if (!$pending_orders_result) {
    die("Error fetching pending orders: " . $conn->error);
}
$pending_orders = $pending_orders_result->fetch_assoc()['pending_orders'];

// Fetch recent orders with customer names
$recent_orders_query = "SELECT o.id, CONCAT(u.first_name, ' ', u.last_name) AS customer_name, o.total_amount, o.status 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC LIMIT 5";
$recent_orders_result = $conn->query($recent_orders_query);
if (!$recent_orders_result) {
    die("Error fetching recent orders: " . $conn->error);
}

// Fetch recent products added
$recent_products_query = "SELECT id, name, price FROM products ORDER BY created_at DESC LIMIT 5";
$recent_products_result = $conn->query($recent_products_query);
if (!$recent_products_result) {
    die("Error fetching recent products: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ThriftX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">

        <!-- Stats Overview -->
        <section class="stats-overview">
            <div class="checkout-section">
                <h2>Dashboard Overview</h2>
                <div class="stats-grid">
                    <div class="stat-box">
                        <h3>Total Sales</h3>
                        <p class="stat-value">৳ <?= number_format($total_sales, 2); ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Total Products</h3>
                        <p class="stat-value"><?= $total_products; ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Pending Orders</h3>
                        <p class="stat-value"><?= $pending_orders; ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Activity -->
        <section class="recent-activity">
            <div class="checkout-section">
                <h2>Recent Orders</h2>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $order['id']; ?></td>
                                    <td><?= htmlspecialchars($order['customer_name']); ?></td>
                                    <td>৳ <?= number_format($order['total_amount'], 2); ?></td>
                                    <td><span class="status-<?= $order['status']; ?>"><?= ucfirst($order['status']); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <h2>Recent Products</h2>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Name</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $recent_products_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $product['id']; ?></td>
                                    <td><?= htmlspecialchars($product['name']); ?></td>
                                    <td>৳ <?= number_format($product['price'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <footer class="footer">
            <p>&copy; 2025 ThriftX. All rights reserved.</p>
        </footer>
    </div> <!-- End page-content -->
</body>
</html>
