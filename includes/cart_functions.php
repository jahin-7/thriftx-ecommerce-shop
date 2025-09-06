<?php
require_once '../config/db.php';

class CartManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Add item to cart
     */
    public function addToCart($user_id, $product_id, $quantity = 1) {
        try {
            // Check if item already exists in cart
            $check_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
            $check_stmt = $this->conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $user_id, $product_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing item quantity
                $existing_item = $result->fetch_assoc();
                $new_quantity = $existing_item['quantity'] + $quantity;
                
                $update_sql = "UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $update_stmt = $this->conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $new_quantity, $existing_item['id']);
                return $update_stmt->execute();
            } else {
                // Insert new item
                $insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
                $insert_stmt = $this->conn->prepare($insert_sql);
                $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
                return $insert_stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Error adding to cart: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update item quantity in cart
     */
    public function updateQuantity($user_id, $product_id, $quantity) {
        try {
            if ($quantity <= 0) {
                return $this->removeFromCart($user_id, $product_id);
            }
            
            $sql = "UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating cart quantity: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove item from cart
     */
    public function removeFromCart($user_id, $product_id) {
        try {
            $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $product_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error removing from cart: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's cart items with product details
     */
    public function getCartItems($user_id) {
        try {
            $sql = "SELECT c.*, p.name, p.price, p.image_url, p.description 
                    FROM cart c 
                    LEFT JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ? 
                    ORDER BY c.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("CartManager: Prepare failed: " . $this->conn->error);
                return [];
            }
            
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                // Only include items where the product exists
                if ($row['name'] !== null) {
                    $items[] = $row;
                } else {
                    // Product doesn't exist, remove from cart
                    $this->removeFromCart($user_id, $row['product_id']);
                }
            }
            
            return $items;
        } catch (Exception $e) {
            error_log("Error getting cart items: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get cart count for user (number of different products)
     */
    public function getCartCount($user_id) {
        try {
            $sql = "SELECT COUNT(*) as total FROM cart WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting cart count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get total quantity of items in cart
     */
    public function getCartTotalQuantity($user_id) {
        try {
            $sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting cart total quantity: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Clear user's cart
     */
    public function clearCart($user_id) {
        try {
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error clearing cart: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Calculate cart total
     */
    public function getCartTotal($user_id) {
        try {
            $sql = "SELECT SUM(p.price * c.quantity) as total 
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error calculating cart total: " . $e->getMessage());
            return 0;
        }
    }
}

// Create global cart manager instance
$cartManager = new CartManager($conn);
?>
