<?php
/**
 * Admin Leave Approval API
 * Endpoint to approve or reject leave requests
 *
 * POST params:
 * - leave_id (int)
 * - action (approve|reject)
 * - admin_comment (optional)
 *
 * Requires admin session (includes/admin_check.php)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/admin_check.php';
require_once __DIR__ . '/../../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'errors' => ['Method not allowed']]);
    exit;
}

$leave_id = isset($_POST['leave_id']) ? intval($_POST['leave_id']) : 0;
$action = isset($_POST['action']) ? strtolower(trim($_POST['action'])) : '';
$admin_comment = isset($_POST['admin_comment']) ? trim($_POST['admin_comment']) : null;

$errors = [];
if ($leave_id <= 0) {
    $errors[] = 'Valid leave_id is required';
}
if (!in_array($action, ['approve', 'reject'])) {
    $errors[] = "Action must be 'approve' or 'reject'";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Map action to status value in DB
$status = $action === 'approve' ? 'Approved' : 'Rejected';

// Verify leave exists
$stmt = $conn->prepare("SELECT id, user_id, status FROM leave_requests WHERE id = ?");
$stmt->bind_param('i', $leave_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    http_response_code(404);
    echo json_encode(['success' => false, 'errors' => ['Leave request not found']]);
    exit;
}
$leave = $result->fetch_assoc();
$stmt->close();

// Optional: prevent approving/rejecting if already decided
if (in_array($leave['status'], ['Approved', 'Rejected'])) {
    http_response_code(409);
    echo json_encode(['success' => false, 'errors' => ['Leave request has already been processed']]);
    exit;
}

// Update leave status and admin comment
$updateStmt = $conn->prepare("UPDATE leave_requests SET status = ?, admin_comment = ? WHERE id = ?");
$updateStmt->bind_param('ssi', $status, $admin_comment, $leave_id);
if ($updateStmt->execute()) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Leave request updated', 'id' => $leave_id, 'status' => $status]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'errors' => ['Failed to update leave request']]);
}
$updateStmt->close();
$conn->close();
?>
