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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = $_POST['role'];
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error_message = "First name, last name, and email are required.";
    } else {
        // Check if email is already taken by another user
        $email_check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $email_stmt = $conn->prepare($email_check_query);
        $email_stmt->bind_param("si", $email, $user_id);
        $email_stmt->execute();
        
        if ($email_stmt->get_result()->num_rows > 0) {
            $error_message = "Email address is already taken by another user.";
        } else {
            // Update user
            $update_query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, role = ?, updated_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $address, $role, $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = "User updated successfully!";
                // Refresh user data
                $user = $stmt->get_result()->fetch_assoc();
            } else {
                $error_message = "Error updating user: " . $update_stmt->error;
            }
        }
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $password_update_query = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $password_stmt = $conn->prepare($password_update_query);
        $password_stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($password_stmt->execute()) {
            $success_message = "Password reset successfully!";
        } else {
            $error_message = "Error resetting password: " . $password_stmt->error;
        }
    }
}

// Get user statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM products WHERE seller_id = ?) as product_count,
    (SELECT COUNT(*) FROM orders WHERE user_id = ?) as order_count,
    (SELECT SUM(total_amount) FROM orders WHERE user_id = ? AND status = 'delivered') as total_spent";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - ThriftX Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Edit User</h1>
                <p>Manage user account information and settings</p>
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

        <div class="edit-user-container">
            <!-- Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22,4 12,14.01 9,11.01"></polyline>
                    </svg>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

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

            <div class="edit-user-grid">
                <!-- User Information Card -->
                <div class="edit-user-card">
                    <div class="card-header">
                        <h3>User Information</h3>
                        <p>Update user's personal details and account settings</p>
                    </div>
                    <div class="card-content">
                        <form method="POST" class="user-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" rows="3" placeholder="Enter user's address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="role">Role *</label>
                                <select id="role" name="role" required>
                                    <option value="customer" <?= $user['role'] == 'customer' ? 'selected' : '' ?>>Customer</option>
                                    <option value="seller" <?= $user['role'] == 'seller' ? 'selected' : '' ?>>Seller</option>
                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="update_user" class="btn btn-primary">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 6L9 17l-5-5"></path>
                                    </svg>
                                    Update User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Password Reset Card -->
                <div class="edit-user-card">
                    <div class="card-header">
                        <h3>Reset Password</h3>
                        <p>Set a new password for this user account</p>
                    </div>
                    <div class="card-content">
                        <form method="POST" class="password-form">
                            <div class="form-group">
                                <label for="new_password">New Password *</label>
                                <input type="password" id="new_password" name="new_password" required minlength="6">
                                <small>Password must be at least 6 characters long</small>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="reset_password" class="btn btn-secondary">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <circle cx="12" cy="16" r="1"></circle>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                    Reset Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- User Statistics Card -->
                <div class="edit-user-card">
                    <div class="card-header">
                        <h3>User Statistics</h3>
                        <p>Account activity and performance metrics</p>
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
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                                <div class="stat-content">
                                    <h4>৳<?= number_format($stats['total_spent'] ?? 0, 2) ?></h4>
                                    <p>Total Spent</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Information Card -->
                <div class="edit-user-card">
                    <div class="card-header">
                        <h3>Account Information</h3>
                        <p>User account details and activity</p>
                    </div>
                    <div class="card-content">
                        <div class="account-info">
                            <div class="info-item">
                                <label>User ID:</label>
                                <span><?= $user['id'] ?></span>
                            </div>
                            <div class="info-item">
                                <label>Current Role:</label>
                                <span class="role-badge role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Account Status:</label>
                                <span class="status-badge status-active">Active</span>
                            </div>
                            <div class="info-item">
                                <label>Member Since:</label>
                                <span><?= date('F j, Y', strtotime($user['created_at'])) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Last Updated:</label>
                                <span><?= date('F j, Y g:i A', strtotime($user['updated_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = this.querySelectorAll('input[required], select[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('error');
                        isValid = false;
                    } else {
                        field.classList.remove('error');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
        });
    </script>
</body>
</html>
