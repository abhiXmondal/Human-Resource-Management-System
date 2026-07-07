<?php
/**
 * Get User Profile API
 * Retrieves user profile information
 */

header('Content-Type: application/json');
require_once '../includes/auth_check.php';
require_once '../config/db_connection.php';

$user_id = $_SESSION['user_id'];

// Query user and profile data
$stmt = $conn->prepare(""
    SELECT 
        u.id, u.employee_id, u.full_name, u.email, u.role, u.created_at,
        ep.phone, ep.address, ep.department, ep.designation, ep.profile_picture, 
        ep.date_of_birth, ep.gender
    FROM users u
    LEFT JOIN employee_profile ep ON u.id = ep.user_id
    WHERE u.id = ?
""");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'errors' => ['User not found']]);
    $stmt->close();
    exit;
}

$profile = $result->fetch_assoc();
$stmt->close();

http_response_code(200);
echo json_encode([
    'success' => true,
    'profile' => $profile
]);

$conn->close();
?>
