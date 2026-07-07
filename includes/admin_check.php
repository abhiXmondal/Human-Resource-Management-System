<?php
/**
 * Admin Authorization Check
 * Include this file to protect admin-only pages
 * 
 * Usage: require_once '../includes/admin_check.php';
 */

require_once '../includes/auth_check.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'errors' => ['Access denied. Admin privileges required.']]);
    exit;
}
?>
