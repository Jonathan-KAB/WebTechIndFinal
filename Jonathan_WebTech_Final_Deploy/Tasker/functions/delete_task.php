<?php
session_start();
require_once '../config/database.php';
require_once 'task_functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'delete_task':
            if (isset($_POST['task_id'])) {
                $success = deleteTask($conn, $_POST['task_id']);
                if (!$success) {
                    http_response_code(500);
                    exit('Failed to delete task');
                }
            }
            break;
            
        case 'delete_project':
            if (isset($_POST['project_id'])) {
                $success = deleteProject($conn, $_POST['project_id']);
                if (!$success) {
                    http_response_code(500);
                    exit('Failed to delete project');
                }
            }
            break;
            
        default:
            http_response_code(400);
            exit('Invalid action');
    }
}
?>