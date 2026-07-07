<?php
/**
 * Leave Request API
 * Handles leave request submission
 */

header('Content-Type: application/json');
require_once '../includes/auth_check.php';
require_once '../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get leave requests for current user
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("""
        SELECT 
            id, leave_type, start_date, end_date, reason, status, admin_comment, applied_at
        FROM leave_requests
        WHERE user_id = ?
        ORDER BY applied_at DESC
    """);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $leaves = [];
    while ($row = $result->fetch_assoc()) {
        $leaves[] = $row;
    }
    
    $stmt->close();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'leaves' => $leaves
    ]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Submit new leave request
    $user_id = $_SESSION['user_id'];
    
    $leave_type = htmlspecialchars(trim($_POST['leave_type'] ?? ''));
    $start_date = htmlspecialchars(trim($_POST['start_date'] ?? ''));
    $end_date = htmlspecialchars(trim($_POST['end_date'] ?? ''));
    $reason = htmlspecialchars(trim($_POST['reason'] ?? ''));
    
    // Validation
    $errors = [];
    
    if (!in_array($leave_type, ['Paid', 'Sick', 'Unpaid'])) {
        $errors[] = "Invalid leave type";
    }
    
    if (empty($start_date) || !strtotime($start_date)) {
        $errors[] = "Valid start date is required";
    }
    
    if (empty($end_date) || !strtotime($end_date)) {
        $errors[] = "Valid end date is required";
    }
    
    if (strtotime($end_date) < strtotime($start_date)) {
        $errors[] = "End date must be after start date";
    }
    
    if (empty($reason)) {
        $errors[] = "Reason for leave is required";
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Insert leave request
    $stmt = $conn->prepare("""
        INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, reason)
        VALUES (?, ?, ?, ?, ?)
    """);
    $stmt->bind_param("issss", $user_id, $leave_type, $start_date, $end_date, $reason);
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Leave request submitted successfully',
            'request_id' => $stmt->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to submit leave request']]);
    }
    
    $stmt->close();

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'errors' => ['Method not allowed']]);
}

$conn->close();
?>
