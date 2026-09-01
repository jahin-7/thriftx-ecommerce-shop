<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');
require_once('../includes/activity_log.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "First name, last name, email, and password are required.";
        header("Location: admin_add_user.php");
        exit;
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: admin_add_user.php");
        exit;
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long.";
        header("Location: admin_add_user.php");
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: admin_add_user.php");
        exit;
    }

    $email_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $email_check->bind_param("s", $email);
    $email_check->execute();

    if ($email_check->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Email address is already registered.";
        header("Location: admin_add_user.php");
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (first_name, last_name, email, password, phone, address, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssss", $first_name, $last_name, $email, $hashed_password, $phone, $address, $role);

    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], 'user_created', 'user', $stmt->insert_id, $email);
        $_SESSION['success'] = "User created successfully!";
        header("Location: admin_users.php");
        exit;
    } else {
        $_SESSION['error'] = "Database error: " . $stmt->error;
        header("Location: admin_add_user.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - ThriftX Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout <?= (($_SESSION['theme'] ?? 'dark') === 'light') ? 'light-theme' : '' ?>">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="checkout-section">
            <div class="admin-page-header">
                <h2>Add New User</h2>
                <a href="admin_users.php" class="back-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12,19 5,12 12,5"></polyline>
                    </svg>
                    Back to Users
                </a>
            </div>

            <!-- Display success/error messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="admin_add_user.php" method="POST" class="checkout-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" required placeholder="Enter first name">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" required placeholder="Enter last name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required placeholder="Enter email address">
                    </div>

                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select id="role" name="role" required>
                            <option value="customer">Customer</option>
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" placeholder="Optional">
                    </div>

                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" placeholder="Optional">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required minlength="6" placeholder="At least 6 characters">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Re-enter password">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="add_user" class="cart-checkout-btn">Create User</button>
                    <a href="admin_users.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
