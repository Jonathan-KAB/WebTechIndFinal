<?php
require_once '../../config/database.php';
require_once 'check_auth.php';
require_once 'task_functions.php';

$projects = getUserProjects($conn, $_SESSION['user_id']);
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_project'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $deadline = $conn->real_escape_string($_POST['deadline']);
        createProject($conn, $user_id, $name, $deadline);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } 
    else if (isset($_POST['create_task'])) {
        $project_id = $conn->real_escape_string($_POST['project_id']);
        $description = $conn->real_escape_string($_POST['description']);
        $start_date = $conn->real_escape_string($_POST['start_date']);
        $end_date = $conn->real_escape_string($_POST['end_date']);
        createTask($conn, $project_id, $description, $start_date, $end_date);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    else if (isset($_POST['update_status'])) {
        $task_id = $conn->real_escape_string($_POST['task_id']);
        $status = $conn->real_escape_string($_POST['status']);
        updateTaskStatus($conn, $task_id, $status);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>