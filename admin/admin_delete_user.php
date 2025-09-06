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

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = '';
$error_message = '';

// Get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: admin_users.php');
    exit;
}

// Prevent admin from deleting themselves
if ($user_id == $_SESSION['user_id']) {
    $error_message = "You cannot delete your own account.";
}

// Get user statistics for confirmation
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM products WHERE seller_id = ?) as product_count,
    (SELECT COUNT(*) FROM orders WHERE user_id = ?) as order_count,
    (SELECT COUNT(*) FROM orders WHERE user_id = ? AND status IN ('pending', 'processing')) as active_orders";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    if ($user_id == $_SESSION['user_id']) {
        $error_message = "You cannot delete your own account.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete user's products first
            $delete_products_query = "DELETE FROM products WHERE seller_id = ?";
            $delete_products_stmt = $conn->prepare($delete_products_query);
            $delete_products_stmt->bind_param("i", $user_id);
            $delete_products_stmt->execute();
            
            // Update orders to remove user reference (set to NULL or keep for historical data)
            $update_orders_query = "UPDATE orders SET user_id = NULL WHERE user_id = ?";
            $update_orders_stmt = $conn->prepare($update_orders_query);
            $update_orders_stmt->bind_param("i", $user_id);
            $update_orders_stmt->execute();
            
            // Delete the user
            $delete_user_query = "DELETE FROM users WHERE id = ?";
            $delete_user_stmt = $conn->prepare($delete_user_query);
            $delete_user_stmt->bind_param("i", $user_id);
            $delete_user_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "User deleted successfully!";
            header('Location: admin_users.php');
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error_message = "Error deleting user: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User - ThriftX Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Delete User</h1>
                <p>Confirm deletion of user account and associated data</p>
            </div>
            <div class="page-actions">
                <a href="admin_users.php" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"></path>
                    </svg>
                    Back to Users
                </a>
            </div>
        </div>

        <div class="delete-user-container">
            <!-- Error Messages -->
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <div class="delete-user-grid">
                <!-- User Information Card -->
                <div class="delete-user-card">
                    <div class="card-header">
                        <h3>User Information</h3>
                        <p>Review user details before deletion</p>
                    </div>
                    <div class="card-content">
                        <div class="user-preview">
                            <div class="user-avatar-large">
                                <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                            </div>
                            <div class="user-details">
                                <h3><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                                <p class="user-email"><?= htmlspecialchars($user['email']) ?></p>
                                <div class="user-badges">
                                    <span class="role-badge role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
                                    <span class="status-badge status-<?= $user['status'] ?>"><?= ucfirst($user['status']) ?></span>
                                </div>
                                <p class="user-join-date">Joined: <?= date('M d, Y', strtotime($user['created_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Statistics Card -->
                <div class="delete-user-card">
                    <div class="card-header">
                        <h3>User Statistics</h3>
                        <p>Data that will be affected by deletion</p>
                    </div>
                    <div class="card-content">
                        <div class="user-stats">
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                    </svg>
                                </div>
                                <div class="stat-content">
                                    <h4><?= number_format($stats['product_count']) ?></h4>
                                    <p>Products Listed</p>
                                </div>
                            </div>

                            <div class="stat-item">
                                <div class="stat-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                    </svg>
                                </div>
                                <div class="stat-content">
                                    <h4><?= number_format($stats['order_count']) ?></h4>
                                    <p>Total Orders</p>
                                </div>
                            </div>

                            <div class="stat-item">
                                <div class="stat-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12,6 12,12 16,14"></polyline>
                                    </svg>
                                </div>
                                <div class="stat-content">
                                    <h4><?= number_format($stats['active_orders']) ?></h4>
                                    <p>Active Orders</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deletion Impact Card -->
                <div class="delete-user-card warning">
                    <div class="card-header">
                        <h3>⚠️ Deletion Impact</h3>
                        <p>This action will permanently remove the following data:</p>
                    </div>
                    <div class="card-content">
                        <div class="impact-list">
                            <div class="impact-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span>User account and profile information</span>
                            </div>
                            <div class="impact-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                    <line x1="3" y1="6" x2="21" y2="6"></line>
                                </svg>
                                <span>All products listed by this user (<?= number_format($stats['product_count']) ?> items)</span>
                            </div>
                            <div class="impact-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                </svg>
                                <span>Order history (user reference will be removed)</span>
                            </div>
                            <div class="impact-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                                <span>This action cannot be undone</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Card -->
                <div class="delete-user-card">
                    <div class="card-header">
                        <h3>Confirm Deletion</h3>
                        <p>Type the user's email to confirm deletion</p>
                    </div>
                    <div class="card-content">
                        <?php if ($user_id == $_SESSION['user_id']): ?>
                            <div class="alert alert-error">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                                You cannot delete your own account. Please ask another admin to perform this action.
                            </div>
                        <?php else: ?>
                            <form method="POST" class="delete-form">
                                <div class="form-group">
                                    <label for="confirm_email">Type user's email to confirm:</label>
                                    <input type="text" id="confirm_email" name="confirm_email" placeholder="Enter: <?= htmlspecialchars($user['email']) ?>" required>
                                    <small>This helps prevent accidental deletions</small>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" name="confirm_delete" class="btn btn-danger" id="delete-btn" disabled>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3,6 5,6 21,6"></polyline>
                                            <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                                        </svg>
                                        Delete User Permanently
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Email confirmation validation
        document.getElementById('confirm_email').addEventListener('input', function() {
            const userEmail = '<?= htmlspecialchars($user['email']) ?>';
            const confirmEmail = this.value.trim();
            const deleteBtn = document.getElementById('delete-btn');
            
            if (confirmEmail === userEmail) {
                deleteBtn.disabled = false;
                this.classList.remove('error');
            } else {
                deleteBtn.disabled = true;
                this.classList.add('error');
            }
        });

        // Form submission confirmation
        document.querySelector('.delete-form').addEventListener('submit', function(e) {
            const userEmail = '<?= htmlspecialchars($user['email']) ?>';
            const confirmEmail = document.getElementById('confirm_email').value.trim();
            
            if (confirmEmail !== userEmail) {
                e.preventDefault();
                alert('Email confirmation does not match. Please enter the correct email address.');
                return;
            }
            
            if (!confirm('Are you absolutely sure you want to delete this user? This action cannot be undone and will permanently remove all associated data.')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
