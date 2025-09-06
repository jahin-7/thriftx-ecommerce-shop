<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');  // Include database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Get product ID from URL
$product_id = $_GET['id'];

// Fetch product details, and ensure it's the user's product (for sellers)
$query = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Product not found or you don't have permission to edit this product.";
    exit;
}

$product = $result->fetch_assoc();

// Handle form submission to update product details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $specifications = $_POST['specifications'];

    // Update product in the database
    $update_query = "UPDATE products SET name = ?, price = ?, category = ?, description = ?, specifications = ?, updated_at = NOW() WHERE id = ? AND seller_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssssi", $name, $price, $category, $description, $specifications, $product_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        echo "Product updated successfully.";
        header("Location: product_page.php?id=$product_id");  // Redirect to product page after successful update
        exit;
    } else {
        echo "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - ThriftX Seller</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Facebook-style Seller Header -->
    <?php include('../includes/seller_header.php'); ?>

    <!-- Page Content -->
    <div class="page-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Edit Product</h1>
                <p>Update your product information and details</p>
            </div>
            <div class="page-actions">
                <a href="seller_products.php" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Products
                </a>
            </div>
        </div>

        <!-- Edit Product Form -->
        <div class="edit-product-container">
            <div class="edit-product-card">
                <div class="card-header">
                    <h3>Product Information</h3>
                    <p>Update your product details and specifications</p>
                </div>
                <div class="card-content">
                    <form action="edit_product.php?id=<?= $product['id']; ?>" method="POST" class="product-form">
                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']); ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Price *</label>
                                <input type="number" id="price" name="price" value="<?= $product['price']; ?>" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select id="category" name="category" required>
                                    <option value="electronics" <?= $product['category'] == 'electronics' ? 'selected' : '' ?>>Electronics</option>
                                    <option value="clothing" <?= $product['category'] == 'clothing' ? 'selected' : '' ?>>Clothing</option>
                                    <option value="furniture" <?= $product['category'] == 'furniture' ? 'selected' : '' ?>>Furniture</option>
                                    <option value="books" <?= $product['category'] == 'books' ? 'selected' : '' ?>>Books</option>
                                    <option value="sports" <?= $product['category'] == 'sports' ? 'selected' : '' ?>>Sports</option>
                                    <option value="other" <?= $product['category'] == 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" rows="4" required placeholder="Describe your product in detail..."><?= htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="specifications">Specifications</label>
                            <textarea id="specifications" name="specifications" rows="3" placeholder="Enter product specifications (optional)"><?= htmlspecialchars($product['specifications'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 6L9 17l-5-5"></path>
                                </svg>
                                Update Product
                            </button>
                            <a href="seller_products.php" class="btn btn-secondary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 12H5M12 19l-7-7 7-7"></path>
                                </svg>
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Product Preview Card -->
            <div class="edit-product-card">
                <div class="card-header">
                    <h3>Product Preview</h3>
                    <p>How your product will appear to customers</p>
                </div>
                <div class="card-content">
                    <div class="product-preview">
                        <div class="product-image">
                            <?php if ($product['image_url']): ?>
                                <img src="<?= htmlspecialchars($product['image_url']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21,15 16,10 5,21"></polyline>
                                    </svg>
                                    <p>No image</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <h4 id="preview-name"><?= htmlspecialchars($product['name']); ?></h4>
                            <p class="product-price" id="preview-price">$<?= number_format($product['price'], 2); ?></p>
                            <p class="product-category" id="preview-category"><?= ucfirst($product['category']); ?></p>
                            <p class="product-description" id="preview-description"><?= htmlspecialchars($product['description']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Live preview updates
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const priceInput = document.getElementById('price');
            const categorySelect = document.getElementById('category');
            const descriptionTextarea = document.getElementById('description');
            
            const previewName = document.getElementById('preview-name');
            const previewPrice = document.getElementById('preview-price');
            const previewCategory = document.getElementById('preview-category');
            const previewDescription = document.getElementById('preview-description');
            
            nameInput.addEventListener('input', function() {
                previewName.textContent = this.value;
            });
            
            priceInput.addEventListener('input', function() {
                previewPrice.textContent = '$' + parseFloat(this.value || 0).toFixed(2);
            });
            
            categorySelect.addEventListener('change', function() {
                previewCategory.textContent = this.options[this.selectedIndex].text;
            });
            
            descriptionTextarea.addEventListener('input', function() {
                previewDescription.textContent = this.value;
            });
        });
    </script>
</body>
</html>
