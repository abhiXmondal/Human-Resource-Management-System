<?php
/**
 * Database Connection Configuration
 * HRMS System Database Handler
 */

// Database credentials
define('DB_HOST', 'localhost');      // Database host
define('DB_USER', 'root');           // Database username
define('DB_PASSWORD', '');           // Database password (empty for default XAMPP/WAMP setup)
define('DB_NAME', 'hrms');           // Database name

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Optional: Set timezone
date_default_timezone_set('UTC');

?>
