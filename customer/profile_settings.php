<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address.";
        } else {
            // Check if email is already taken by another user
            $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_email->bind_param("si", $email, $user_id);
            $check_email->execute();
            $result = $check_email->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "This email is already taken by another user.";
            } else {
                // Update user profile
                $update_query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $address, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['last_name'] = $last_name;
                    $_SESSION['email'] = $email;
                    $success_message = "Profile updated successfully!";
                } else {
                    $error_message = "Error updating profile: " . $stmt->error;
                }
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Get current password hash
        $get_password = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $get_password->bind_param("i", $user_id);
        $get_password->execute();
        $result = $get_password->get_result();
        $user = $result->fetch_assoc();
        
        if (!password_verify($current_password, $user['password'])) {
            $error_message = "Current password is incorrect.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $error_message = "New password must be at least 6 characters long.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_password->bind_param("si", $hashed_password, $user_id);
            
            if ($update_password->execute()) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Error changing password: " . $update_password->error;
            }
        }
    }
}

// Get current user data
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get order statistics
$order_stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(total_amount) as total_spent,
    AVG(total_amount) as avg_order_value
    FROM orders WHERE user_id = ?";
$order_stmt = $conn->prepare($order_stats_query);
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_stats = $order_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile & Settings - ThriftX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Sidebar Toggle -->
    <input id="sidebar-toggle" type="checkbox">
    <label class="toggle" for="sidebar-toggle">
        <div class="bars"></div>
        <div class="bars"></div>
        <div class="bars"></div>
    </label>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-section">
                <div class="logo-icon">T</div>
                <span class="logo-text">ThriftX</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="cart.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span>Shopping Cart</span>
            </a>
            <a href="electronics.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
                <span>Electronics</span>
            </a>
            <a href="clothing.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.38 3.46L16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"></path>
                </svg>
                <span>Clothing</span>
            </a>
            <a href="furniture.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"></path>
                    <path d="M8 21V8a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v13"></path>
                </svg>
                <span>Furniture</span>
            </a>
            <a href="services.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Services</span>
            </a>
            <a href="profile_settings.php" class="nav-item active">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Profile & Settings</span>
            </a>
            <a href="../logout.php" class="nav-item logout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16,17 21,12 16,7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <!-- Page Content -->
    <div class="page-content">
        <!-- Header -->
        <header class="customer-header">
            <div class="header-left">
                <div class="page-title">
                    <h1>Profile & Settings</h1>
                    <p>Manage your account information and preferences</p>
                </div>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="back-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <div class="checkout-section">
            <!-- Display messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22,4 12,14.01 9,11.01"></polyline>
                    </svg>
                    <?= $success_message ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <div class="settings-grid">
                <!-- Profile Information Card -->
                <div class="settings-card">
                    <div class="card-header">
                        <h2>Profile Information</h2>
                        <p>Update your personal details</p>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" required 
                                       value="<?= htmlspecialchars($user['first_name']) ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" required 
                                       value="<?= htmlspecialchars($user['last_name']) ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" required 
                                       value="<?= htmlspecialchars($user['email']) ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3" 
                                      placeholder="Enter your full address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="submit-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17,21 17,13 7,13 7,21"></polyline>
                                    <polyline points="7,3 7,8 15,8"></polyline>
                                </svg>
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Card -->
                <div class="settings-card">
                    <div class="card-header">
                        <h2>Change Password</h2>
                        <p>Update your account password</p>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="form-group">
                            <label for="current_password">Current Password *</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">New Password *</label>
                                <input type="password" id="new_password" name="new_password" required minlength="6">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="submit-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <circle cx="12" cy="16" r="1"></circle>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Account Statistics Card -->
                <div class="settings-card">
                    <div class="card-header">
                        <h2>Order Statistics</h2>
                        <p>Your shopping history and statistics</p>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?= $order_stats['total_orders'] ?? 0 ?></div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-value">৳ <?= number_format($order_stats['total_spent'] ?? 0, 2) ?></div>
                            <div class="stat-label">Total Spent</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-value">৳ <?= number_format($order_stats['avg_order_value'] ?? 0, 2) ?></div>
                            <div class="stat-label">Avg Order Value</div>
                        </div>
                    </div>
                </div>

                <!-- Account Information Card -->
                <div class="settings-card">
                    <div class="card-header">
                        <h2>Account Information</h2>
                        <p>Your account details</p>
                    </div>
                    
                    <div class="account-info">
                        <div class="info-item">
                            <label>Account Type:</label>
                            <span class="role-badge role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
                        </div>
                        
                        <div class="info-item">
                            <label>Member Since:</label>
                            <span><?= date('F d, Y', strtotime($user['created_at'])) ?></span>
                        </div>
                        
                        <div class="info-item">
                            <label>Last Updated:</label>
                            <span><?= date('F d, Y', strtotime($user['updated_at'] ?? $user['created_at'])) ?></span>
                        </div>
                        
                        <div class="info-item">
                            <label>User ID:</label>
                            <span>#<?= $user['id'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
