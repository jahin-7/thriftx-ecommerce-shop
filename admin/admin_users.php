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
$role = isset($_GET['role']) ? $_GET['role'] : '';

// Build query
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= "sss";
}

if (!empty($role)) {
    $where_conditions[] = "role = ?";
    $params[] = $role;
    $param_types .= "s";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM users $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_users = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

// Fetch users
$query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
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
    <title>Admin Users - ThriftX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="checkout-section">
            <div class="admin-page-header">
                <h2>User Management</h2>
                <a href="admin_add_user.php" class="add-product-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add New User
                </a>
            </div>

            <!-- Search and Filters -->
            <div class="admin-filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <input type="text" name="search" placeholder="Search users..." 
                               value="<?= htmlspecialchars($search) ?>" class="search-input">
                    </div>
                    <div class="filter-group">
                        <select name="role" class="filter-select">
                            <option value="">All Roles</option>
                            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="seller" <?= $role === 'seller' ? 'selected' : '' ?>>Seller</option>
                            <option value="customer" <?= $role === 'customer' ? 'selected' : '' ?>>Customer</option>
                        </select>
                    </div>
                    <button type="submit" class="filter-btn">Filter</button>
                    <a href="admin_users.php" class="clear-btn">Clear</a>
                </form>
            </div>

            <!-- Users Grid -->
            <div class="admin-products-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <div class="admin-product-card">
                            <div class="user-avatar-section">
                                <div class="user-avatar-large">
                                    <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                                </div>
                                <div class="user-role-badge role-<?= $user['role'] ?>">
                                    <?= ucfirst($user['role']) ?>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                                <p class="user-email"><?= htmlspecialchars($user['email']) ?></p>
                                <p class="user-join-date">Joined: <?= date('M d, Y', strtotime($user['created_at'])) ?></p>
                                <div class="product-actions">
                                    <a href="admin_edit_user.php?id=<?= $user['id'] ?>" class="edit-btn">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                        Edit
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="admin_delete_user.php?id=<?= $user['id'] ?>" 
                                           class="delete-btn" 
                                           onclick="return confirm('Are you sure you want to delete this user?')">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3,6 5,6 21,6"></polyline>
                                                <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                                            </svg>
                                            Delete
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-products">
                        <h3>No users found</h3>
                        <p>Try adjusting your search criteria or add a new user.</p>
                        <a href="admin_add_user.php" class="add-product-btn">Add First User</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="admin-pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($role) ? '&role=' . urlencode($role) : '' ?>" class="page-btn">First</a>
                        <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($role) ? '&role=' . urlencode($role) : '' ?>" class="page-btn">Prev</a>
                    <?php endif; ?>
                    
                    <span class="page-info">Page <?= $page ?> of <?= $total_pages ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($role) ? '&role=' . urlencode($role) : '' ?>" class="page-btn">Next</a>
                        <a href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($role) ? '&role=' . urlencode($role) : '' ?>" class="page-btn">Last</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
