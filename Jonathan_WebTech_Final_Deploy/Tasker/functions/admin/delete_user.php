<?php
require_once '../../config/database.php';
require_once '../../auth/admin_check.php';

header('Content-Type: application/json');

$response = ['success' => false];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $conn->real_escape_string($data['id']);

    // Prevent deleting the current admin
    if ($userId == $_SESSION['user_id']) {
        throw new Exception('Cannot delete current user');
    }

    // Delete user and associated data
    $conn->begin_transaction();

    // Delete tasks
    $conn->query("DELETE t FROM tasks t JOIN projects p ON t.project_id = p.id WHERE p.user_id = '$userId'");
    
    // Delete projects
    $conn->query("DELETE FROM projects WHERE user_id = '$userId'");
    
    // Delete user
    $conn->query("DELETE FROM users WHERE id = '$userId'");

    $conn->commit();

    $response['success'] = true;
    $response['message'] = 'User deleted successfully';
} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>