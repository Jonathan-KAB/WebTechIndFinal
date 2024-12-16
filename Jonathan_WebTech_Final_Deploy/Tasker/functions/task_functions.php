<?php
function createProject($conn, $userId, $name, $deadline) {
    $sql = "INSERT INTO projects (user_id, name, deadline) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $userId, $name, $deadline);
    return $stmt->execute();
}

function createTask($conn, $projectId, $description, $startDate, $endDate) {
    $sql = "INSERT INTO tasks (project_id, description, start_date, end_date, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $projectId, $description, $startDate, $endDate);
    return $stmt->execute();
}

function getUserProjects($conn, $userId) {
    $sql = "SELECT * FROM projects WHERE user_id = ? ORDER BY deadline";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result();
}

function getProjectTasks($conn, $projectId) {
    $sql = "SELECT * FROM tasks WHERE project_id = ? ORDER BY start_date";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    return $stmt->get_result();
}

function updateTaskStatus($conn, $taskId, $status) {
    $sql = "UPDATE tasks SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $taskId);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Failed to update task status: " . $stmt->error);
    }
    return $result;
}

function getTaskStats($conn, $userId) {
    $stats = [
        'totalTasks' => 0,
        'completedTasks' => 0,
        'pendingTasks' => 0,
        'inProgressTasks' => 0
    ];
    
    $allProjects = getUserProjects($conn, $userId);
    while ($project = $allProjects->fetch_assoc()) {
        $projectTasks = getProjectTasks($conn, $project['id']);
        while ($task = $projectTasks->fetch_assoc()) {
            $stats['totalTasks']++;
            switch ($task['status']) {
                case 'completed':
                    $stats['completedTasks']++;
                    break;
                case 'pending':
                    $stats['pendingTasks']++;
                    break;
                case 'in-progress':
                    $stats['inProgressTasks']++;
                    break;
            }
        }
    }
    
    return $stats;
}

function deleteTask($conn, $taskId) {
    // First verify the task exists and belongs to the current user
    $sql = "DELETE t FROM tasks t 
            INNER JOIN projects p ON t.project_id = p.id 
            WHERE t.id = ? AND p.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $taskId, $_SESSION['user_id']);
    return $stmt->execute();
}

function deleteProject($conn, $projectId) {
    // First verify the project belongs to the current user
    $sql = "SELECT user_id FROM projects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();
    
    if ($project['user_id'] != $_SESSION['user_id']) {
        return false;
    }

    // Start transaction
    $conn->begin_transaction();
    try {
        // Delete all tasks in the project
        $sql = "DELETE FROM tasks WHERE project_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $projectId);
        $stmt->execute();

        // Delete the project
        $sql = "DELETE FROM projects WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $projectId);
        $stmt->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function updateTask($conn, $taskId, $description, $startDate, $endDate) {
    // Verify task belongs to the current user
    $sql = "UPDATE tasks t 
            INNER JOIN projects p ON t.project_id = p.id 
            SET t.description = ?, t.start_date = ?, t.end_date = ? 
            WHERE t.id = ? AND p.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $description, $startDate, $endDate, $taskId, $_SESSION['user_id']);
    return $stmt->execute();
}

function updateProject($conn, $projectId, $name, $deadline) {
    // Verify project belongs to current user
    $sql = "UPDATE projects 
            SET name = ?, deadline = ? 
            WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $name, $deadline, $projectId, $_SESSION['user_id']);
    return $stmt->execute();
}

?>