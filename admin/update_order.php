<?php
session_start();

// Check if user is logged in (either admin or seller)
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../index.php');
    exit;
}

include('../config/db.php');  // Include database connection
require_once('../includes/activity_log.php');

// Fetch the logged-in user's role and ID
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Only admins and sellers may update order status
if ($user_role !== 'admin' && $user_role !== 'seller') {
    header('Location: ../index.php');
    exit;
}

// Check if form data is present
if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Validate status against the actual orders.status ENUM
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        echo "Invalid status.";
        exit;
    }

    // If the user is a seller, they can only update orders that contain at
    // least one of their own products (orders has no seller_id column, so
    // ownership is derived through order_items -> products.seller_id)
    if ($user_role == 'seller') {
        $query = "SELECT DISTINCT o.id FROM orders o
                  JOIN order_items oi ON oi.order_id = o.id
                  JOIN products p ON p.id = oi.product_id
                  WHERE o.id = ? AND p.seller_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $order_result = $stmt->get_result();

        if ($order_result->num_rows == 0) {
            // If the order doesn't belong to the seller, show an error
            echo "You are not authorized to edit this order.";
            exit;
        }
    }

    // Update the order status in the database
    $query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        logActivity($conn, $user_id, 'order_status_changed', 'order', $order_id, $status);
        // Redirect back to the orders listing the update was submitted from
        if ($user_role == 'admin') {
            header('Location: admin_orders.php');
        } else {
            header('Location: ../seller/seller_orders.php');
        }
        exit;
    } else {
        // Error handling if the update fails
        echo "Error updating order status: " . $conn->error;
    }
} else {
    // If the required parameters are not passed, show an error
    echo "Missing order ID or status.";
    exit;
}
?>
