<?php
include('../config/db.php'); // Include the database connection file

// Start session for better error handling
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $specifications = $_POST['specifications'];

    // Handle image upload
    $target_dir = "uploads/"; // Set the directory to upload the images
    
    // Generate unique filename to avoid conflicts
    $original_name = basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $unique_filename = time() . '_' . uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $unique_filename;

    // Check if the image file is a valid image (optional)
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        $_SESSION['error'] = "File is not an image.";
        header("Location: post_product.php");
        exit;
    }

    // Check file size (limit to 10MB for example)
    if ($_FILES["image"]["size"] > 10000000) {
        $_SESSION['error'] = "Sorry, your file is too large. Maximum size is 10MB.";
        header("Location: post_product.php");
        exit;
    }

    // Allow only certain file formats (e.g., jpg, png, jpeg)
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        $_SESSION['error'] = "Sorry, only JPG, JPEG, & PNG files are allowed.";
        header("Location: post_product.php");
        exit;
    }

    // Try to upload the file
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Use prepared statements to insert product details safely into the database
        // First, check if specifications column exists
        $check_column = $conn->query("SHOW COLUMNS FROM products LIKE 'specifications'");
        
        if ($check_column->num_rows > 0) {
            // Specifications column exists, include it in the query
            $query = "INSERT INTO products (name, description, price, category, image_url, specifications, seller_id) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            
            // Get seller ID from session (assuming user is logged in as seller)
            $seller_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to 1 if no session
            
            $stmt->bind_param("ssisssi", $name, $description, $price, $category, $target_file, $specifications, $seller_id);
        } else {
            // Specifications column doesn't exist, exclude it from the query
            $query = "INSERT INTO products (name, description, price, category, image_url, seller_id) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            
            // Get seller ID from session (assuming user is logged in as seller)
            $seller_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to 1 if no session
            
            $stmt->bind_param("ssissi", $name, $description, $price, $category, $target_file, $seller_id);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = "Product added successfully!";
            header("Location: seller_dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = "Database error: " . $stmt->error;
            header("Location: post_product.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Sorry, there was an error uploading your file.";
        header("Location: post_product.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Product - ThriftX Seller</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Facebook-style Seller Header -->
    <?php include('../includes/seller_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Post New Product</h1>
                <p>Add a new product to your store</p>
            </div>
            <div class="page-actions">
                <a href="seller_dashboard.php" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="checkout-section">
            <!-- Display success/error messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22,4 12,14.01 9,11.01"></polyline>
                    </svg>
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Product Form Card -->
            <div class="product-form-card">
                <div class="card-header">
                    <h2>Product Information</h2>
                    <p>Fill in the details below to add your product</p>
                </div>
                
                <form action="post_product.php" method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" required placeholder="Enter product name">
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category *</label>
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
                            <label for="price">Price (৳) *</label>
                            <input type="number" id="price" name="price" required placeholder="0.00" min="0" step="0.01">
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Product Image *</label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="image" name="image" accept="image/*" required>
                                <label for="image" class="file-upload-label">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7,10 12,15 17,10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    <span>Choose Image</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Product Description *</label>
                        <textarea id="description" name="description" rows="4" required placeholder="Describe your product in detail..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="specifications">Product Specifications *</label>
                        <textarea id="specifications" name="specifications" rows="4" required placeholder="Add technical specifications, features, dimensions, etc..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" onclick="window.history.back()" class="cancel-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            Cancel
                        </button>
                        <button type="submit" name="submit" class="submit-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17,21 17,13 7,13 7,21"></polyline>
                                <polyline points="7,3 7,8 15,8"></polyline>
                            </svg>
                            Post Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
