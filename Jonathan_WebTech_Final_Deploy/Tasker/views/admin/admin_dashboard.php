<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/database.php";
require_once "../../auth/dashboard_session.php";
require_once "../../functions/admin/admin_functions.php";
require_once "../../auth/admin_check.php";

// Verify admin access
requireAdmin($conn, $_SESSION['user_id']);

// Get analytics data
$totalUsers = getTotalUsers($conn);
$totalProjects = getTotalProjects($conn);
$totalTasks = getTotalTasksAllUsers($conn);
$tasksByStatus = getTasksByStatus($conn);
$projectsByDeadline = getProjectsByDeadline($conn);
$userActivity = getUserActivity($conn);
$upcomingProjects = getUpcomingProjects($conn);
$upcomingDeadlines = getUpcomingDeadlines($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z"></script>
    <title>Admin Dashboard - Tasker Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="../../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="admin-nav-container">
            <div class="admin-nav-menu">
                <h1 class="text-xl font-bold">Tasker Admin</h1>
                <div class="flex items-center gap-4">
                    <a href="#" class="admin-nav-link active">Dashboard</a>
                    <a href="reports.php" class="admin-nav-link">Reports</a>
                    <a href="users.php" class="admin-nav-link">Users</a>
                    <a href="../user/dashboard.php" class="admin-nav-link">User Dashboard</a>
                    <a href="../../auth/logout.php" class="admin-button admin-button-danger">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-8">
        <!-- Statistics Overview -->
        <div class="stat-grid mb-8">
            <div class="stat-card">
                <p class="stat-label">Total Users</p>
                <p class="stat-value"><?php echo $totalUsers; ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Total Projects</p>
                <p class="stat-value"><?php echo $totalProjects; ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Total Tasks</p>
                <p class="stat-value"><?php echo $totalTasks; ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Active Projects</p>
                <p class="stat-value"><?php echo getActiveProjects($conn); ?></p>
            </div>
        </div>

        <!-- Charts -->
        <!-- Upcoming Projects and Deadlines -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <!-- Upcoming Projects -->
            <div class="admin-card">
                <h3 class="chart-title">Upcoming Projects</h3>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Owner</th>
                                <th>Deadline</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $upcomingProjects = getUpcomingProjects($conn);
                            while ($project = $upcomingProjects->fetch_assoc()):
                                $progress = $project['total_tasks'] > 0 
                                    ? round(($project['completed_tasks'] / $project['total_tasks']) * 100)
                                    : 0;
                                $progressClass = $progress < 33 ? 'progress-bar-fill-low' : 
                                            ($progress < 66 ? 'progress-bar-fill-medium' : 'progress-bar-fill-high');
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['name']); ?></td>
                                <td><?php echo htmlspecialchars($project['username']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($project['deadline'])); ?></td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-bar-fill <?php echo $progressClass; ?>" 
                                            style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 mt-1 inline-block"><?php echo $progress; ?>%</span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Upcoming Deadlines -->
            <div class="admin-card">
                <h3 class="chart-title">Upcoming Task Deadlines</h3>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Assigned To</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $upcomingDeadlines = getUpcomingDeadlines($conn);
                            while ($task = $upcomingDeadlines->fetch_assoc()):
                                $daysUntilDue = (strtotime($task['end_date']) - time()) / (60 * 60 * 24);
                                $dateClass = $daysUntilDue <= 2 ? 'text-red-600' : 
                                        ($daysUntilDue <= 5 ? 'text-yellow-600' : 'text-gray-600');
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                <td><?php echo htmlspecialchars($task['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($task['username']); ?></td>
                                <td class="<?php echo $dateClass; ?>">
                                    <?php echo date('M d, Y', strtotime($task['end_date'])); ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="admin-card">
            <h3 class="chart-title">Recent Activity</h3>
            <div class="activity-feed">
                <?php foreach ($userActivity as $activity): ?>
                    <div class="activity-item">
                        <div class="flex justify-between items-center">
                            <span><?php echo htmlspecialchars($activity['username']); ?>: 
                                  <?php echo htmlspecialchars($activity['action']); ?></span>
                            <span class="activity-meta">
                                <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/admin_charts.js"></script>
</body>
</html>