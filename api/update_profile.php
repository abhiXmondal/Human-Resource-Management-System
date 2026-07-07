<?php
/**
 * Update User Profile API
 * Updates user profile information
 */

header('Content-Type: application/json');
require_once '../includes/auth_check.php';
require_once '../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'errors' => ['Method not allowed']]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get and sanitize input
$phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
$address = htmlspecialchars(trim($_POST['address'] ?? ''));
$department = htmlspecialchars(trim($_POST['department'] ?? ''));
$designation = htmlspecialchars(trim($_POST['designation'] ?? ''));
$date_of_birth = htmlspecialchars(trim($_POST['date_of_birth'] ?? ''));
$gender = htmlspecialchars(trim($_POST['gender'] ?? ''));

// Update or insert employee profile
$stmt = $conn->prepare(""
    INSERT INTO employee_profile (user_id, phone, address, department, designation, date_of_birth, gender)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
    phone = VALUES(phone),
    address = VALUES(address),
    department = VALUES(department),
    designation = VALUES(designation),
    date_of_birth = VALUES(date_of_birth),
    gender = VALUES(gender)
""");

$stmt->bind_param("issssss", $user_id, $phone, $address, $department, $designation, $date_of_birth, $gender);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'errors' => ['Failed to update profile']]);
}

$stmt->close();
$conn->close();
?>
