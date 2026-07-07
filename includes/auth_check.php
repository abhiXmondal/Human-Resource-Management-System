<?php
/**
 * Authentication Check
 * Include this file to protect pages that require authentication
 * 
 * Usage: require_once '../includes/auth_check.php';
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: ../login.html');
    exit;
}

// Optional: Check session timeout (30 minutes)
$session_timeout = 1800; // 30 minutes
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $session_timeout) {
    session_destroy();
    header('Location: ../login.html?timeout=1');
    exit;
}

// Update last activity time
$_SESSION['login_time'] = time();
?>
