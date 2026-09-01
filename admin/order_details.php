<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

include('../config/db.php');  // Include database connection

// Get order ID from URL
$order_id = $_GET['id'];

// Fetch order details
$query = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

// Fetch order items (products)
$order_items_query = "SELECT oi.*, p.name, p.price FROM order_items oi
                       JOIN products p ON oi.product_id = p.id
                       WHERE oi.order_id = ?";
$order_items_stmt = $conn->prepare($order_items_query);
$order_items_stmt->bind_param("i", $order_id);
$order_items_stmt->execute();
$order_items_result = $order_items_stmt->get_result();

if ($order_result->num_rows == 0) {
    echo "Order not found.";
    exit;
}

$order = $order_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="../assets/css/styles.css"> <!-- Your CSS file -->
</head>
<body class="<?= (($_SESSION['theme'] ?? 'dark') === 'light') ? 'light-theme' : '' ?>">

<!-- Admin Header -->
<header class="header">
    <div class="logo">
        <a href="mockup.php">
            <h1>ThriftX - Admin</h1>
        </a>
    </div>
    <nav class="nav">
        <a href="admin_orders.php">Orders</a>
        <a href="admin_products.php">Products</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<!-- Order Details Section -->
<section class="order-details">
    <h2>Order Details</h2>
    <p><strong>Order ID:</strong> <?= (int) $order['id']; ?></p>
    <p><strong>Customer Name:</strong> <?= htmlspecialchars($order['name']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']); ?></p>
    <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order['address']); ?></p>
    <p><strong>Order Date:</strong> <?= htmlspecialchars($order['order_date']); ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($order['status']); ?></p>
    <h3>Products in this Order</h3>
    <table>
        <tr>
            <th>Product Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
        <?php while ($item = $order_items_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']); ?></td>
                <td>৳ <?= number_format((float) $item['price'], 2); ?></td>
                <td><?= (int) $item['quantity']; ?></td>
                <td>৳ <?= number_format((float) $item['price'] * (int) $item['quantity'], 2); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <p><strong>Total Amount:</strong> ৳ <?= number_format((float) $order['total'], 2); ?></p>
</section>

</body>
</html>
