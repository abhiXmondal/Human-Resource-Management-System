<?php
/**
 * User Login Handler
 * Handles user authentication and session management
 */

header('Content-Type: application/json');
session_start();
require_once '../config/db_connection.php';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get and sanitize input
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Validation
    $errors = [];

    if (empty($email)) {
        $errors[] = "Email is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    // Return validation errors
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Query user from database
    $stmt = $conn->prepare("SELECT id, employee_id, full_name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'errors' => ['Invalid email or password']]);
        $stmt->close();
        exit;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify password
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'errors' => ['Invalid email or password']]);
        exit;
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['employee_id'] = $user['employee_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['login_time'] = time();

    // Prepare response
    $response = [
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'employee_id' => $user['employee_id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ];

    // Redirect based on role
    if ($user['role'] === 'admin') {
        $response['redirect'] = '../admindashboard.html';
    } else {
        $response['redirect'] = '../employee_dashboard.html';
    }

    http_response_code(200);
    echo json_encode($response);

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'errors' => ['Method not allowed']]);
}

$conn->close();
?>
