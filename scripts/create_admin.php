<?php
/**
 * Helper script to create an initial admin account.
 * Usage: php scripts/create_admin.php
 *
 * This script uses config/db_connection.php for DB credentials. It will prompt
 * interactively for an email and password if run from CLI. It will create a user
 * with role = 'admin' and a corresponding empty employee_profile row.
 */

require_once __DIR__ . '/../config/db_connection.php';

// simple interactive prompt
function prompt($msg){
    if(PHP_SAPI === 'cli'){
        echo $msg . ': ';
        return trim(fgets(STDIN));
    }
    return null;
}

$email = prompt('Admin email (e.g. admin@example.com)');
$full_name = prompt('Full name for admin');
$password = prompt('Password (will be hashed)');
$employee_id = prompt('Employee ID (e.g. ADM-001)');

if(empty($email) || empty($password)){
    echo "Email and password are required. Exiting.\n";
    exit(1);
}

$hashed = password_hash($password, PASSWORD_BCRYPT);

// insert user
$stmt = $conn->prepare('INSERT INTO users (employee_id, full_name, email, password, role) VALUES (?, ?, ?, ?, ?);');
$role = 'admin';
$stmt->bind_param('sssss', $employee_id, $full_name, $email, $hashed, $role);

if(!$stmt->execute()){
    echo "Failed to create admin user: " . $stmt->error . "\n";
    exit(1);
}

$user_id = $stmt->insert_id;
$stmt->close();

// create empty profile row
$profileStmt = $conn->prepare('INSERT INTO employee_profile (user_id) VALUES (?);');
$profileStmt->bind_param('i', $user_id);
$profileStmt->execute();
$profileStmt->close();

echo "Admin user created successfully with ID: $user_id\n";
echo "You can now login with the provided credentials.\n";

$conn->close();
?>
