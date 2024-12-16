<?php
require_once '../../config/database.php';
require_once '../../auth/admin_check.php';

header('Content-Type: application/json');

// Validate input
$response = ['success' => false];

try {
    $userId = $_POST['user_id'] ?? null;
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);
    $password = $_POST['password'] ?? null;

    if (empty($username) || empty($email) || empty($role)) {
        throw new Exception('Missing required fields');
    }

    if ($userId) {
        // Update existing user
        $sql = "UPDATE users SET username = '$username', email = '$email', role = '$role' WHERE id = '$userId'";
    } else {
        // Create new user
        if (empty($password)) {
            throw new Exception('Password is required for new users');
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashedPassword', '$role')";
    }

    if ($conn->query($sql)) {
        $response['success'] = true;
        $response['message'] = $userId ? 'User updated successfully' : 'User created successfully';
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>