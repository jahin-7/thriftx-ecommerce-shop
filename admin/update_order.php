<?php
session_start();

// Check if user is logged in (either admin or seller)
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../index.php');
    exit;
}

include('../config/db.php');  // Include database connection

// Fetch the logged-in user's role and ID
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];  // 'admin' or 'seller'

// Check if form data is present
if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Validate status
    $valid_statuses = ['pending', 'shipped', 'delivered', 'canceled'];
    if (!in_array($status, $valid_statuses)) {
        echo "Invalid status.";
        exit;
    }

    // If the user is a seller, they can only update their own orders
    if ($user_role == 'seller') {
        // Query to check if the order belongs to the seller
        $query = "SELECT * FROM orders WHERE id = ? AND seller_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $order_id, $user_id);  // Make sure the order belongs to the logged-in seller
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
        // Redirect to the orders page (or seller dashboard) after successful update
        if ($user_role == 'admin') {
            header('Location: admin_orders.php');  // Admin's redirect
        } else {
            header('Location: seller_dashboard.php');  // Seller's redirect
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
