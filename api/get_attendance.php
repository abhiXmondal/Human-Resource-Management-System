<?php
/**
 * Get Attendance Records API
 * Retrieves attendance records for a user
 */

header('Content-Type: application/json');
require_once '../includes/auth_check.php';
require_once '../config/db_connection.php';

$user_id = $_SESSION['user_id'];
$month = htmlspecialchars(trim($_GET['month'] ?? date('Y-m')));

// Validate month format (YYYY-MM)
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => ['Invalid month format']]);
    exit;
}

// Query attendance records
$stmt = $conn->prepare(""
    SELECT 
        id, attendance_date, check_in, check_out, status, remarks
    FROM attendance
    WHERE user_id = ? AND DATE_FORMAT(attendance_date, '%Y-%m') = ?
    ORDER BY attendance_date DESC
""");
$stmt->bind_param("is", $user_id, $month);
$stmt->execute();
$result = $stmt->get_result();

$attendance = [];
while ($row = $result->fetch_assoc()) {
    $attendance[] = $row;
}

$stmt->close();

http_response_code(200);
echo json_encode([
    'success' => true,
    'month' => $month,
    'attendance' => $attendance,
    'count' => count($attendance)
]);

$conn->close();
?>
