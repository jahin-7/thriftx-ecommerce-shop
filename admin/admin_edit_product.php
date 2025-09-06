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

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = '';
$error_message = '';

// Get product details
$product_query = "SELECT p.*, u.first_name, u.last_name 
                  FROM products p 
                  LEFT JOIN users u ON p.seller_id = u.id 
                  WHERE p.id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: admin_products.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category = $_POST['category'];
    $status = $_POST['status'];
    $specifications = trim($_POST['specifications']);
    
    // Validate inputs
    if (empty($name) || empty($description) || $price <= 0 || empty($category)) {
        $error_message = "Please fill in all required fields with valid data.";
    } else {
        // Handle image upload if new image is provided
        $image_url = $product['image_url']; // Keep existing image by default
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../seller/uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Generate unique filename
            $original_name = basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $unique_filename = time() . '_' . uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $unique_filename;
            
            // Validate image
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                $error_message = "File is not an image.";
            } elseif ($_FILES["image"]["size"] > 10000000) {
                $error_message = "File is too large. Maximum size is 10MB.";
            } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
                $error_message = "Only JPG, JPEG, and PNG files are allowed.";
            } else {
                // Upload new image
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    // Delete old image if it exists
                    if ($product['image_url'] && file_exists($product['image_url'])) {
                        unlink($product['image_url']);
                    }
                    $image_url = "seller/uploads/" . $unique_filename;
                } else {
                    $error_message = "Error uploading image.";
                }
            }
        }
        
        if (empty($error_message)) {
            // Check if specifications column exists
            $check_column = $conn->query("SHOW COLUMNS FROM products LIKE 'specifications'");
            
            if ($check_column->num_rows > 0) {
                // Update with specifications
                $update_query = "UPDATE products SET name = ?, description = ?, price = ?, category = ?, status = ?, image_url = ?, specifications = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ssdssssi", $name, $description, $price, $category, $status, $image_url, $specifications, $product_id);
            } else {
                // Update without specifications
                $update_query = "UPDATE products SET name = ?, description = ?, price = ?, category = ?, status = ?, image_url = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ssdsssi", $name, $description, $price, $category, $status, $image_url, $product_id);
            }
            
            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Product updated successfully!";
                header('Location: admin_products.php');
                exit;
            } else {
                $error_message = "Error updating product: " . $update_stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - ThriftX Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="checkout-section">
            <div class="admin-page-header">
                <h2>Edit Product</h2>
                <a href="admin_products.php" class="back-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Products
                </a>
            </div>

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

            <!-- Product Form Card -->
            <div class="product-form-card">
                <div class="card-header">
                    <h2>Edit Product Information</h2>
                    <p>Update the product details below</p>
                </div>
                
                <form action="admin_edit_product.php?id=<?= $product_id ?>" method="POST" enctype="multipart/form-data" class="product-form">
                    <input type="hidden" name="update_product" value="1">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category *</label>
                            <select id="category" name="category" required>
                                <option value="">Select a category</option>
                                <option value="electronics" <?= $product['category'] === 'electronics' ? 'selected' : '' ?>>Electronics</option>
                                <option value="clothing" <?= $product['category'] === 'clothing' ? 'selected' : '' ?>>Clothing</option>
                                <option value="furniture" <?= $product['category'] === 'furniture' ? 'selected' : '' ?>>Furniture</option>
                                <option value="services" <?= $product['category'] === 'services' ? 'selected' : '' ?>>Services</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price (৳) *</label>
                            <input type="number" id="price" name="price" required 
                                   value="<?= $product['price'] ?>" min="0" step="0.01">
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="pending" <?= $product['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <div class="current-image">
                            <?php if ($product['image_url'] && file_exists("../" . $product['image_url'])): ?>
                                <div class="image-preview">
                                    <img src="../<?= htmlspecialchars($product['image_url']) ?>" alt="Current image">
                                    <p>Current image</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="file-upload-wrapper">
                            <input type="file" id="image" name="image" accept="image/*">
                            <label for="image" class="file-upload-label">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7,10 12,15 17,10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                <span>Choose New Image (Optional)</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Product Description *</label>
                        <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="specifications">Product Specifications</label>
                        <textarea id="specifications" name="specifications" rows="4"><?= htmlspecialchars($product['specifications'] ?? '') ?></textarea>
                    </div>

                    <!-- Product Info -->
                    <div class="product-info-section">
                        <h3>Product Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Product ID:</label>
                                <span>#<?= $product['id'] ?></span>
                            </div>
                            <div class="info-item">
                                <label>Seller:</label>
                                <span><?= htmlspecialchars($product['first_name'] . ' ' . $product['last_name']) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Created:</label>
                                <span><?= date('M d, Y', strtotime($product['created_at'])) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Last Updated:</label>
                                <span><?= date('M d, Y', strtotime($product['updated_at'] ?? $product['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="admin_products.php" class="cancel-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            Cancel
                        </a>
                        <button type="submit" class="submit-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17,21 17,13 7,13 7,21"></polyline>
                                <polyline points="7,3 7,8 15,8"></polyline>
                            </svg>
                            Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
