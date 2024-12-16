<?php
// Admin Functions
function getTotalUsers($conn) {
    $sql = "SELECT COUNT(*) as total FROM users";
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    return $data['total'];
}

function getTotalProjects($conn) {
    $sql = "SELECT COUNT(*) as total FROM projects";
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    return $data['total'];
}

function getTotalTasksAllUsers($conn) {
    $sql = "SELECT COUNT(*) as total FROM tasks";
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    return $data['total'];
}

function getTasksByStatus($conn) {
    $sql = "SELECT status, COUNT(*) as count 
            FROM tasks 
            GROUP BY status 
            ORDER BY status";
    $result = $conn->query($sql);
    $tasksByStatus = [];
    while ($row = $result->fetch_assoc()) {
        $tasksByStatus[$row['status']] = $row['count'];
    }
    return $tasksByStatus;
}

function getProjectsByDeadline($conn) {
    $sql = "SELECT p.name, p.deadline, u.username, 
            (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) as total_tasks,
            (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.status = 'completed') as completed_tasks
            FROM projects p
            JOIN users u ON p.user_id = u.id
            WHERE p.deadline >= CURDATE()
            ORDER BY p.deadline ASC
            LIMIT 10";
    $result = $conn->query($sql);
    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
    return $projects;
}

function getUserActivity($conn) {
    $sql = "SELECT u.username, t.status, t.description, p.name as project_name, t.created_at
            FROM tasks t
            JOIN projects p ON t.project_id = p.id
            JOIN users u ON p.user_id = u.id
            ORDER BY t.created_at DESC
            LIMIT 20";
    $result = $conn->query($sql);
    $activity = [];
    while ($row = $result->fetch_assoc()) {
        $activity[] = [
            'username' => $row['username'],
            'action' => sprintf("%s task '%s' in project '%s' marked as %s",
                              $row['status'],
                              $row['description'],
                              $row['project_name'],
                              $row['status']),
            'created_at' => $row['created_at']
        ];
    }
    return $activity;
}

function getActiveProjects($conn) {
    $sql = "SELECT COUNT(*) as total 
            FROM projects p
            WHERE p.deadline >= CURDATE()
            AND EXISTS (
                SELECT 1 FROM tasks t 
                WHERE t.project_id = p.id 
                AND t.status IN ('pending', 'in-progress')
            )";
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    return $data['total'];
}

function getProjectProgressStats($conn) {
    $sql = "SELECT p.id, p.name,
            COUNT(*) as total_tasks,
            SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks
            FROM projects p
            LEFT JOIN tasks t ON p.id = t.project_id
            GROUP BY p.id, p.name";
    $result = $conn->query($sql);
    $stats = [];
    while ($row = $result->fetch_assoc()) {
        $stats[] = [
            'name' => $row['name'],
            'progress' => $row['total_tasks'] > 0 
                ? round(($row['completed_tasks'] / $row['total_tasks']) * 100)
                : 0
        ];
    }
    return $stats;
}

function getUserProductivity($conn) {
    $sql = "SELECT u.username,
            COUNT(*) as total_tasks,
            SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
            AVG(CASE WHEN t.status = 'completed' THEN 
                DATEDIFF(t.updated_at, t.created_at)
                ELSE NULL END) as avg_completion_time
            FROM users u
            JOIN projects p ON u.id = p.user_id
            JOIN tasks t ON p.id = t.project_id
            GROUP BY u.id, u.username";
    $result = $conn->query($sql);
    $productivity = [];
    while ($row = $result->fetch_assoc()) {
        $productivity[] = $row;
    }
    return $productivity;
}

function getUpcomingProjects($conn) {
    $sql = "SELECT p.name, p.deadline, u.username,
            COUNT(t.id) as total_tasks,
            SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks
            FROM projects p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN tasks t ON p.id = t.project_id
            WHERE p.deadline >= CURDATE()
            GROUP BY p.id
            ORDER BY p.deadline ASC
            LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->get_result();
}

function getUpcomingDeadlines($conn) {
    $sql = "SELECT p.name as project_name, p.deadline,
            t.description as task_name, t.end_date,
            u.username
            FROM tasks t
            JOIN projects p ON t.project_id = p.id
            JOIN users u ON p.user_id = u.id
            WHERE t.end_date >= CURDATE()
            AND t.status != 'completed'
            ORDER BY t.end_date ASC
            LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->get_result();
}

function getAllUsers($conn) {
    $sql = "SELECT DISTINCT
            u.id, 
            u.username, 
            u.email, 
            u.role, 
            u.created_at,
            (SELECT COUNT(*) FROM projects WHERE user_id = u.id) as project_count,
            (SELECT MAX(created_at) FROM projects WHERE user_id = u.id) as last_activity
            FROM users u
            ORDER BY u.created_at DESC";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            error_log("getAllUsers query failed: " . $conn->error);
            return false;
        }
        return $result;
    } catch (Exception $e) {
        error_log("getAllUsers error: " . $e->getMessage());
        return false;
    }
}

function getActiveUsers($conn) {
    $sql = "SELECT COUNT(DISTINCT u.id) as active_users
            FROM users u
            JOIN projects p ON u.id = p.user_id
            WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    return $data['active_users'];
}

function getAdminCount($conn) {
    $sql = "SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'";
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    return $data['admin_count'];
}

function getNewUsersCount($conn, $days) {
    $sql = "SELECT COUNT(*) as new_users 
            FROM users 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $days);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['new_users'];
}

function getUserProjectCount($conn, $userId) {
    $sql = "SELECT COUNT(*) as project_count FROM projects WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['project_count'];
}

function getUserActivities($conn, $limit = 20) {
    $sql = "SELECT 
                u.username,
                'created project' as action,
                p.name as project_name,
                p.created_at
            FROM projects p
            JOIN users u ON p.user_id = u.id
            UNION ALL
            SELECT 
                u.username,
                CONCAT('updated task in project') as action,
                p.name as project_name,
                t.created_at
            FROM tasks t
            JOIN projects p ON t.project_id = p.id
            JOIN users u ON p.user_id = u.id
            ORDER BY created_at DESC
            LIMIT ?";

    try {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            return [];
        }

        if (!$stmt->bind_param("i", $limit)) {
            error_log("Binding parameters failed: " . $stmt->error);
            return [];
        }

        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return [];
        }

        $result = $stmt->get_result();
        $activities = [];
        
        while ($row = $result->fetch_assoc()) {
            $activities[] = [
                'username' => $row['username'],
                'action' => $row['action'] . ' ' . $row['project_name'],
                'created_at' => $row['created_at']
            ];
        }
        
        return $activities;
    } catch (Exception $e) {
        error_log("Error in getUserActivities: " . $e->getMessage());
        return [];
    }
}

function updateUser($conn, $userId, $data) {
    $sql = "UPDATE users 
            SET username = ?, email = ?, role = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", 
        $data['username'], 
        $data['email'], 
        $data['role'],
        $userId
    );
    return $stmt->execute();
}

function deleteUser($conn, $userId) {
    // Start transaction
    $conn->begin_transaction();
    try {
        // Delete user's tasks
        $sql = "DELETE t FROM tasks t 
                JOIN projects p ON t.project_id = p.id 
                WHERE p.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // Delete user's projects
        $sql = "DELETE FROM projects WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // Delete the user
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function searchUsers($conn, $query) {
    $searchTerm = "%$query%";
    $sql = "SELECT u.*, COUNT(p.id) as project_count
            FROM users u
            LEFT JOIN projects p ON u.id = p.user_id
            WHERE u.username LIKE ? OR u.email LIKE ?
            GROUP BY u.id
            ORDER BY u.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    return $stmt->get_result();
}

function getLastUserActivity($conn, $userId) {
    $sql = "SELECT 
            GREATEST(
                COALESCE(
                    (SELECT MAX(created_at) 
                     FROM projects 
                     WHERE user_id = ?),
                    '1970-01-01'
                ),
                COALESCE(
                    (SELECT MAX(t.created_at) 
                     FROM tasks t 
                     JOIN projects p ON t.project_id = p.id 
                     WHERE p.user_id = ?),
                    '1970-01-01'
                )
            ) as last_activity";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    // Calculate days since last activity
    if ($data['last_activity'] === '1970-01-01') {
        return 999; // No activity found
    }
    
    $lastActivity = new DateTime($data['last_activity']);
    $now = new DateTime();
    return $lastActivity->diff($now)->days;
}

?>