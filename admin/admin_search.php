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

$query_text = isset($_GET['q']) ? trim($_GET['q']) : '';
$users = [];
$products = [];
$orders = [];

if ($query_text !== '') {
    $like = '%' . $query_text . '%';

    $user_stmt = $conn->prepare("SELECT id, first_name, last_name, email, role FROM users
                                  WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?
                                  ORDER BY created_at DESC LIMIT 10");
    $user_stmt->bind_param("sss", $like, $like, $like);
    $user_stmt->execute();
    $users = $user_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $product_stmt = $conn->prepare("SELECT id, name, price, category, status FROM products
                                     WHERE name LIKE ? OR description LIKE ? OR category LIKE ?
                                     ORDER BY created_at DESC LIMIT 10");
    $product_stmt->bind_param("sss", $like, $like, $like);
    $product_stmt->execute();
    $products = $product_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Orders are matched by numeric ID or by customer name/email
    $order_stmt = $conn->prepare("SELECT o.id, o.total_amount, o.status, CONCAT(u.first_name, ' ', u.last_name) AS customer_name
                                   FROM orders o LEFT JOIN users u ON o.user_id = u.id
                                   WHERE o.id = ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?
                                   ORDER BY o.created_at DESC LIMIT 10");
    $order_id_match = ctype_digit($query_text) ? (int)$query_text : 0;
    $order_stmt->bind_param("isss", $order_id_match, $like, $like, $like);
    $order_stmt->execute();
    $orders = $order_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$total_results = count($users) + count($products) + count($orders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - ThriftX Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout <?= (($_SESSION['theme'] ?? 'dark') === 'light') ? 'light-theme' : '' ?>">
    <?php include('../includes/admin_header.php'); ?>

    <div class="page-content">
        <div class="checkout-section">
            <div class="admin-page-header">
                <h2>Search Results<?= $query_text !== '' ? ' for "' . htmlspecialchars($query_text) . '"' : '' ?></h2>
            </div>

            <?php if ($query_text === ''): ?>
                <div class="no-products">
                    <h3>Enter a search term</h3>
                    <p>Search across users, products, and orders using the box above.</p>
                </div>
            <?php elseif ($total_results === 0): ?>
                <div class="no-products">
                    <h3>No results found</h3>
                    <p>Nothing matched "<?= htmlspecialchars($query_text) ?>". Try a different term.</p>
                </div>
            <?php else: ?>

                <?php if (!empty($users)): ?>
                    <h3>Users</h3>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr><th>Name</th><th>Email</th><th>Role</th><th></th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td><span class="role-badge role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                                        <td><a href="admin_edit_user.php?id=<?= $u['id'] ?>" class="edit-btn">Edit</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (!empty($products)): ?>
                    <h3>Products</h3>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr><th>Name</th><th>Category</th><th>Price</th><th>Status</th><th></th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['name']) ?></td>
                                        <td><?= ucfirst($p['category']) ?></td>
                                        <td>৳ <?= number_format($p['price'], 2) ?></td>
                                        <td><span class="status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                                        <td><a href="admin_edit_product.php?id=<?= $p['id'] ?>" class="edit-btn">Edit</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (!empty($orders)): ?>
                    <h3>Orders</h3>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th></th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td>#<?= $o['id'] ?></td>
                                        <td><?= htmlspecialchars($o['customer_name'] ?? 'Unknown') ?></td>
                                        <td>৳ <?= number_format($o['total_amount'], 2) ?></td>
                                        <td><span class="status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                                        <td><a href="order_details.php?id=<?= $o['id'] ?>" class="edit-btn">View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>
