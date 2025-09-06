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

$success_message = '';
$error_message = '';

// Handle system settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    $site_name = trim($_POST['site_name']);
    $site_email = trim($_POST['site_email']);
    $site_phone = trim($_POST['site_phone']);
    $site_address = trim($_POST['site_address']);
    $currency = trim($_POST['currency']);
    $tax_rate = floatval($_POST['tax_rate']);
    $shipping_fee = floatval($_POST['shipping_fee']);
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
    $registration_enabled = isset($_POST['registration_enabled']) ? 1 : 0;
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    
    // Validate required fields
    if (empty($site_name) || empty($site_email)) {
        $error_message = "Site name and email are required.";
    } else {
        // Update settings in database (assuming you have a settings table)
        // For now, we'll store in session or create a simple settings file
        $settings = [
            'site_name' => $site_name,
            'site_email' => $site_email,
            'site_phone' => $site_phone,
            'site_address' => $site_address,
            'currency' => $currency,
            'tax_rate' => $tax_rate,
            'shipping_fee' => $shipping_fee,
            'maintenance_mode' => $maintenance_mode,
            'registration_enabled' => $registration_enabled,
            'email_notifications' => $email_notifications,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Store settings in a JSON file (you can modify this to use database)
        if (file_put_contents('../config/settings.json', json_encode($settings))) {
            $success_message = "Settings updated successfully!";
        } else {
            $error_message = "Error updating settings. Please check file permissions.";
        }
    }
}

// Load current settings
$settings_file = '../config/settings.json';
$current_settings = [
    'site_name' => 'ThriftX',
    'site_email' => 'admin@thriftx.com',
    'site_phone' => '',
    'site_address' => '',
    'currency' => 'USD',
    'tax_rate' => 0.0,
    'shipping_fee' => 0.0,
    'maintenance_mode' => 0,
    'registration_enabled' => 1,
    'email_notifications' => 1
];

if (file_exists($settings_file)) {
    $saved_settings = json_decode(file_get_contents($settings_file), true);
    if ($saved_settings) {
        $current_settings = array_merge($current_settings, $saved_settings);
    }
}

// Get system statistics
$stats = [];
$stats['total_users'] = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$stats['total_products'] = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$stats['total_orders'] = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$stats['pending_orders'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - ThriftX Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="page-header">
            <div class="page-title">
                <h1>System Settings</h1>
                <p>Configure your marketplace settings and system preferences</p>
            </div>
        </div>

        <div class="settings-container">
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

            <div class="settings-grid">
                <!-- General Settings -->
                <div class="settings-card">
                    <div class="card-header">
                        <h3>General Settings</h3>
                        <p>Basic information about your marketplace</p>
                    </div>
                    <div class="card-content">
                        <form method="POST" class="settings-form">
                            <div class="form-group">
                                <label for="site_name">Site Name *</label>
                                <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($current_settings['site_name']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="site_email">Site Email *</label>
                                <input type="email" id="site_email" name="site_email" value="<?= htmlspecialchars($current_settings['site_email']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="site_phone">Contact Phone</label>
                                <input type="tel" id="site_phone" name="site_phone" value="<?= htmlspecialchars($current_settings['site_phone']) ?>">
                            </div>

                            <div class="form-group">
                                <label for="site_address">Business Address</label>
                                <textarea id="site_address" name="site_address" rows="3" placeholder="Enter your business address"><?= htmlspecialchars($current_settings['site_address']) ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="currency">Currency</label>
                                    <select id="currency" name="currency">
                                        <option value="USD" <?= $current_settings['currency'] == 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                        <option value="EUR" <?= $current_settings['currency'] == 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                                        <option value="GBP" <?= $current_settings['currency'] == 'GBP' ? 'selected' : '' ?>>GBP (£)</option>
                                        <option value="CAD" <?= $current_settings['currency'] == 'CAD' ? 'selected' : '' ?>>CAD (C$)</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Financial Settings -->
                <div class="settings-card">
                    <div class="card-header">
                        <h3>Financial Settings</h3>
                        <p>Configure pricing and payment settings</p>
                    </div>
                    <div class="card-content">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tax_rate">Tax Rate (%)</label>
                                <input type="number" id="tax_rate" name="tax_rate" value="<?= $current_settings['tax_rate'] ?>" step="0.01" min="0" max="100">
                                <small>Enter as percentage (e.g., 8.5 for 8.5%)</small>
                            </div>
                            <div class="form-group">
                                <label for="shipping_fee">Default Shipping Fee</label>
                                <input type="number" id="shipping_fee" name="shipping_fee" value="<?= $current_settings['shipping_fee'] ?>" step="0.01" min="0">
                                <small>Default shipping cost for orders</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Settings -->
                <div class="settings-card">
                    <div class="card-header">
                        <h3>System Settings</h3>
                        <p>Control system behavior and features</p>
                    </div>
                    <div class="card-content">
                        <div class="toggle-group">
                            <div class="toggle-item">
                                <div class="toggle-info">
                                    <h4>Maintenance Mode</h4>
                                    <p>Enable to temporarily disable the site for maintenance</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="maintenance_mode" <?= $current_settings['maintenance_mode'] ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="toggle-item">
                                <div class="toggle-info">
                                    <h4>User Registration</h4>
                                    <p>Allow new users to register accounts</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="registration_enabled" <?= $current_settings['registration_enabled'] ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="toggle-item">
                                <div class="toggle-info">
                                    <h4>Email Notifications</h4>
                                    <p>Send email notifications for orders and updates</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="email_notifications" <?= $current_settings['email_notifications'] ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Statistics -->
                <div class="settings-card">
                    <div class="card-header">
                        <h3>System Statistics</h3>
                        <p>Current system metrics and performance</p>
                    </div>
                    <div class="card-content">
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <div class="stat-content">
                                    <h4><?= number_format($stats['total_users']) ?></h4>
                                    <p>Total Users</p>
                                </div>
                            </div>

                            <div class="stat-item">
                                <div class="stat-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                    </svg>
                                </div>
                                <div class="stat-content">
                                    <h4><?= number_format($stats['total_products']) ?></h4>
                                    <p>Total Products</p>
                                </div>
                            </div>

                            <div class="stat-item">
                                <div class="stat-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                    </svg>
                                </div>
                                <div class="stat-content">
                                    <h4><?= number_format($stats['total_orders']) ?></h4>
                                    <p>Total Orders</p>
                                </div>
                            </div>

                            <div class="stat-item">
                                <div class="stat-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12,6 12,12 16,14"></polyline>
                                    </svg>
                                </div>
                                <div class="stat-content">
                                    <h4><?= number_format($stats['pending_orders']) ?></h4>
                                    <p>Pending Orders</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="settings-actions">
                <button type="submit" form="settings-form" class="btn btn-primary btn-large">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17,21 17,13 7,13 7,21"></polyline>
                        <polyline points="7,3 7,8 15,8"></polyline>
                    </svg>
                    Save All Settings
                </button>
            </div>
        </div>
    </div>

    <script>
        // Form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.settings-form');
            const saveButton = document.querySelector('.settings-actions button');
            
            saveButton.addEventListener('click', function() {
                // Add all form data to the form
                const formData = new FormData();
                
                // Add all input values
                form.querySelectorAll('input, textarea, select').forEach(input => {
                    if (input.type === 'checkbox') {
                        formData.append(input.name, input.checked ? '1' : '0');
                    } else {
                        formData.append(input.name, input.value);
                    }
                });
                
                // Add the submit button
                formData.append('update_settings', '1');
                
                // Submit the form
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'admin_settings.php', true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        location.reload();
                    }
                };
                xhr.send(formData);
            });
        });
    </script>
</body>
</html>
