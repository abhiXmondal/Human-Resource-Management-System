<?php
/**
 * User Registration/Signup Handler
 * Handles user signup with validation and database insertion
 */

header('Content-Type: application/json');
require_once '../config/db_connection.php';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get and sanitize input
    $employee_id = htmlspecialchars(trim($_POST['employee_id'] ?? ''));
    $full_name = htmlspecialchars(trim($_POST['full_name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role = htmlspecialchars(trim($_POST['role'] ?? 'employee'));

    // Validation
    $errors = [];

    if (empty($employee_id)) {
        $errors[] = "Employee ID is required";
    }

    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }

    // Password validation (8+ chars, uppercase, number, special character)
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    } elseif (!preg_match('/[!@#$%^&*(),.?\":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }

    if (!in_array($role, ['employee', 'admin'])) {
        $errors[] = "Invalid role selected";
    }

    // Return validation errors
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Check if employee_id already exists
    $check_employee = $conn->prepare("SELECT id FROM users WHERE employee_id = ?");
    $check_employee->bind_param("s", $employee_id);
    $check_employee->execute();
    if ($check_employee->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => ['Employee ID already exists']]);
        $check_employee->close();
        exit;
    }
    $check_employee->close();

    // Check if email already exists
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    if ($check_email->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => ['Email already registered']]);
        $check_email->close();
        exit;
    }
    $check_email->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO users (employee_id, full_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $employee_id, $full_name, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Create employee profile entry
        $profile_stmt = $conn->prepare("INSERT INTO employee_profile (user_id) VALUES (?)");
        $profile_stmt->bind_param("i", $user_id);
        $profile_stmt->execute();
        $profile_stmt->close();

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully. Please verify your email.',
            'user_id' => $user_id,
            'email' => $email
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to create account']]);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'errors' => ['Method not allowed']]);
}

$conn->close();
?>
