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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $status = $_POST['status'];
    $specifications = $_POST['specifications'] ?? '';
    $seller_id = $_POST['seller_id'] ?? $_SESSION['user_id']; // Default to admin if no seller selected

    // Handle image upload
    $target_dir = "../seller/uploads/";
    $target_file = '';
    
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $original_name = basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $unique_filename = time() . '_' . uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $unique_filename;

        // Check if the image file is a valid image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['error'] = "File is not an image.";
            header("Location: admin_add_product.php");
            exit;
        }

        // Check file size (limit to 10MB)
        if ($_FILES["image"]["size"] > 10000000) {
            $_SESSION['error'] = "Sorry, your file is too large. Maximum size is 10MB.";
            header("Location: admin_add_product.php");
            exit;
        }

        // Allow only certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $_SESSION['error'] = "Sorry, only JPG, JPEG, & PNG files are allowed.";
            header("Location: admin_add_product.php");
            exit;
        }

        // Try to upload the file
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $_SESSION['error'] = "Sorry, there was an error uploading your file.";
            header("Location: admin_add_product.php");
            exit;
        }
    } else {
        // Use placeholder image if no image uploaded
        $target_file = 'https://via.placeholder.com/300x200?text=No+Image';
    }

    // Insert product into database
    $query = "INSERT INTO products (name, description, price, category, image_url, specifications, seller_id, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssisssis", $name, $description, $price, $category, $target_file, $specifications, $seller_id, $status);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product added successfully!";
        header("Location: admin_products.php");
        exit;
    } else {
        $_SESSION['error'] = "Database error: " . $stmt->error;
        header("Location: admin_add_product.php");
        exit;
    }
}

// Get all sellers for dropdown
$sellers_query = "SELECT id, first_name, last_name FROM users WHERE role = 'seller' ORDER BY first_name";
$sellers_result = $conn->query($sellers_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - ThriftX Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-layout">
    <!-- Facebook-style Admin Header -->
    <?php include('../includes/admin_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="checkout-section">
            <div class="admin-page-header">
                <h2>Add New Product</h2>
                <a href="admin_products.php" class="back-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12,19 5,12 12,5"></polyline>
                    </svg>
                    Back to Products
                </a>
            </div>
            
            <!-- Display success/error messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success" style="background: #4CAF50; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error" style="background: #f44336; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <form action="admin_add_product.php" method="POST" enctype="multipart/form-data" class="checkout-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Product Name:</label>
                        <input type="text" id="name" name="name" required placeholder="Enter product name">
                    </div>

                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <option value="electronics">Electronics</option>
                            <option value="clothing">Clothing</option>
                            <option value="furniture">Furniture</option>
                            <option value="services">Services</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (৳):</label>
                        <input type="number" id="price" name="price" required placeholder="Enter price in BDT" min="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="sold">Sold</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="seller_id">Assign to Seller:</label>
                    <select id="seller_id" name="seller_id">
                        <option value="<?= $_SESSION['user_id'] ?>">Admin (Self)</option>
                        <?php while ($seller = $sellers_result->fetch_assoc()): ?>
                            <option value="<?= $seller['id'] ?>">
                                <?= htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="image">Product Image:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small style="color: var(--font-color-sub);">Optional - Leave empty to use placeholder image</small>
                </div>

                <div class="form-group">
                    <label for="description">Product Description:</label>
                    <textarea id="description" name="description" rows="4" required placeholder="Describe the product..."></textarea>
                </div>

                <div class="form-group">
                    <label for="specifications">Product Specifications:</label>
                    <textarea id="specifications" name="specifications" rows="4" placeholder="Add technical specifications, features, etc..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="cart-checkout-btn">Add Product</button>
                    <a href="admin_products.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
