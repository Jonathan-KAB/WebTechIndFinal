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
        case 'update_task':
            if (isset($_POST['task_id'], $_POST['description'], $_POST['start_date'], $_POST['end_date'])) {
                $success = updateTask(
                    $conn,
                    $_POST['task_id'],
                    $_POST['description'],
                    $_POST['start_date'],
                    $_POST['end_date']
                );
                if (!$success) {
                    http_response_code(500);
                    exit('Failed to update task');
                }
            }
            break;
            
        case 'update_project':
            if (isset($_POST['project_id'], $_POST['name'], $_POST['deadline'])) {
                $success = updateProject(
                    $conn,
                    $_POST['project_id'],
                    $_POST['name'],
                    $_POST['deadline']
                );
                if (!$success) {
                    http_response_code(500);
                    exit('Failed to update project');
                }
            }
            break;
            
        default:
            http_response_code(400);
            exit('Invalid action');
    }
}
?>