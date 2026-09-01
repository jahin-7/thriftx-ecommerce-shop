<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current = ($_SESSION['theme'] ?? 'dark') === 'light' ? 'light' : 'dark';
$_SESSION['theme'] = $current === 'light' ? 'dark' : 'light';

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
// Only allow redirecting back to a same-site relative path, never an external URL
if (preg_match('/^https?:\/\//i', $redirect) || strpos($redirect, '//') === 0) {
    $redirect = 'index.php';
}

header('Location: ' . $redirect);
exit;
?>
