<?php
// Authentication helper functions

function requireLogin() {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php');
        exit();
    }
}

function requireRole($required_role) {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php');
        exit();
    }
    
    if ($_SESSION['role'] !== $required_role) {
        header('Location: ../index.php');
        exit();
    }
}

function requireAdmin() {
    requireRole('admin');
}

function requireSeller() {
    requireRole('seller');
}

function requireCustomer() {
    requireRole('customer');
}

function isLoggedIn() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role'],
        'first_name' => $_SESSION['first_name'],
        'last_name' => $_SESSION['last_name']
    ];
}

function logout() {
    session_start();
    session_destroy();
    header('Location: ../index.php');
    exit();
}

function getFullName() {
    session_start();
    
    if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
        return $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    }
    
    return 'User';
}
?>
