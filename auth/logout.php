<?php
/**
 * User Logout Handler
 * Destroys user session and redirects to login
 */

session_start();

// Clear session variables
$_SESSION = [];

// Destroy session
session_destroy();

// Redirect to login page
header('Location: ../login.html');
exit;
?>
