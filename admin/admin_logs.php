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

// Handle filters
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';

$where_clause = '';
$params = [];
$param_types = '';
if (!empty($action_filter)) {
    $where_clause = "WHERE l.action = ?";
    $params[] = $action_filter;
    $param_types .= "s";
}

// Pagination
$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(*) as total FROM activity_logs l $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_logs = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_logs / $limit);

$query = "SELECT l.*, u.first_name, u.last_name, u.email
          FROM activity_logs l
          LEFT JOIN users u ON l.user_id = u.id
          $where_clause
          ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $all_params = array_merge($params, [$limit, $offset]);
    $stmt->bind_param($param_types . "ii", ...$all_params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// Distinct actions for the filter dropdown
$actions_result = $conn->query("SELECT DISTINCT action FROM activity_logs ORDER BY action");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - ThriftX Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout <?= (($_SESSION['theme'] ?? 'dark') === 'light') ? 'light-theme' : '' ?>">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="checkout-section">
            <div class="admin-page-header">
                <h2>Activity Logs</h2>
            </div>

            <!-- Filters -->
            <div class="admin-filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <select name="action" class="filter-select">
                            <option value="">All Actions</option>
                            <?php while ($a = $actions_result->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($a['action']) ?>" <?= $action_filter === $a['action'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $a['action']))) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="filter-btn">Filter</button>
                    <a href="admin_logs.php" class="clear-btn">Clear</a>
                </form>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Target</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($log = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('M d, Y g:i A', strtotime($log['created_at'])) ?></td>
                                    <td><?= $log['first_name'] ? htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) : '<em>Deleted user</em>' ?></td>
                                    <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $log['action']))) ?></td>
                                    <td><?= $log['target_type'] ? htmlspecialchars(ucfirst($log['target_type']) . ($log['target_id'] ? ' #' . $log['target_id'] : '')) : '-' ?></td>
                                    <td><?= htmlspecialchars($log['details'] ?? '') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="admin-pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1<?= !empty($action_filter) ? '&action=' . urlencode($action_filter) : '' ?>" class="page-btn">First</a>
                            <a href="?page=<?= $page - 1 ?><?= !empty($action_filter) ? '&action=' . urlencode($action_filter) : '' ?>" class="page-btn">Prev</a>
                        <?php endif; ?>

                        <span class="page-info">Page <?= $page ?> of <?= $total_pages ?></span>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?><?= !empty($action_filter) ? '&action=' . urlencode($action_filter) : '' ?>" class="page-btn">Next</a>
                            <a href="?page=<?= $total_pages ?><?= !empty($action_filter) ? '&action=' . urlencode($action_filter) : '' ?>" class="page-btn">Last</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-products">
                    <h3>No activity yet</h3>
                    <p>Admin and seller actions (products, users, orders, settings) will appear here as they happen.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
