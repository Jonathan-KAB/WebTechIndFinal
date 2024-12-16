<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/database.php";
require_once "../../auth/dashboard_session.php";
require_once "../../auth/admin_check.php";
require_once "../../functions/admin/admin_functions.php";

// Verify admin access
requireAdmin($conn, $_SESSION['user_id']);

// Get period for reports (default to last 30 days)
$period = isset($_GET['period']) ? $_GET['period'] : '30';

// Get report data
$totalTasks = getTotalTasksAllUsers($conn);
$tasksByStatus = getTasksByStatus($conn);
$projectProgress = getProjectProgressStats($conn);
$userProductivity = getUserProductivity($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z"></script>
    <title>Reports - Tasker Admin</title>
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
                    <a href="admin_dashboard.php" class="admin-nav-link">Dashboard</a>
                    <a href="reports.php" class="admin-nav-link active">Reports</a>
                    <a href="users.php" class="admin-nav-link">Users</a>
                    <a href="../user/dashboard.php" class="admin-nav-link">User Dashboard</a>
                    <a href="../../auth/logout.php" class="admin-button admin-button-danger">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-8">
        <!-- Time Period Selector -->
        <div class="mb-8">
            <form class="flex gap-4 items-center">
                <label class="font-medium">Time Period:</label>
                <select name="period" class="rounded-md border-gray-300 shadow-sm" 
                        onchange="this.form.submit()">
                    <option value="7" <?php echo $period == '7' ? 'selected' : ''; ?>>Last 7 Days</option>
                    <option value="30" <?php echo $period == '30' ? 'selected' : ''; ?>>Last 30 Days</option>
                    <option value="90" <?php echo $period == '90' ? 'selected' : ''; ?>>Last 90 Days</option>
                    <option value="all" <?php echo $period == 'all' ? 'selected' : ''; ?>>All Time</option>
                </select>
            </form>
        </div>

        <!-- Task Status Overview -->
        <div class="admin-card mb-8">
            <h2 class="chart-title">Task Status Distribution</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600">Completed Tasks</p>
                    <p class="text-3xl font-bold text-green-600">
                        <?php echo $tasksByStatus['completed'] ?? 0; ?>
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">In Progress Tasks</p>
                    <p class="text-3xl font-bold text-yellow-600">
                        <?php echo $tasksByStatus['in-progress'] ?? 0; ?>
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Pending Tasks</p>
                    <p class="text-3xl font-bold text-red-600">
                        <?php echo $tasksByStatus['pending'] ?? 0; ?>
                    </p>
                </div>
            </div>
            <div class="mt-6 h-64">
                <canvas id="taskStatusChart"></canvas>
            </div>
        </div>

        <!-- Project Progress -->
        <div class="admin-card mb-8">
            <h2 class="chart-title">Project Progress</h2>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projectProgress as $project): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($project['name']); ?></td>
                            <td class="w-1/2">
                                <div class="progress-bar">
                                    <div class="progress-bar-fill <?php 
                                        echo $project['progress'] < 33 ? 'progress-bar-fill-low' : 
                                             ($project['progress'] < 66 ? 'progress-bar-fill-medium' : 
                                              'progress-bar-fill-high'); 
                                    ?>" style="width: <?php echo $project['progress']; ?>%"></div>
                                </div>
                            </td>
                            <td><?php echo $project['progress']; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Productivity -->
        <div class="admin-card">
            <h2 class="chart-title">User Productivity</h2>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Total Tasks</th>
                            <th>Completed Tasks</th>
                            <th>Completion Rate</th>
                            <th>Avg. Completion Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userProductivity as $user): 
                            $completionRate = $user['total_tasks'] > 0 
                                ? round(($user['completed_tasks'] / $user['total_tasks']) * 100, 1)
                                : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo $user['total_tasks']; ?></td>
                            <td><?php echo $user['completed_tasks']; ?></td>
                            <td>
                                <div class="progress-bar w-32">
                                    <div class="progress-bar-fill <?php 
                                        echo $completionRate < 33 ? 'progress-bar-fill-low' : 
                                             ($completionRate < 66 ? 'progress-bar-fill-medium' : 
                                              'progress-bar-fill-high'); 
                                    ?>" style="width: <?php echo $completionRate; ?>%"></div>
                                </div>
                                <span class="text-xs ml-2"><?php echo $completionRate; ?>%</span>
                            </td>
                            <td><?php echo round($user['avg_completion_time'], 1); ?> days</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const taskStatusData = {
            completed: <?php echo $tasksByStatus['completed'] ?? 0; ?>,
            inProgress: <?php echo $tasksByStatus['in-progress'] ?? 0; ?>,
            pending: <?php echo $tasksByStatus['pending'] ?? 0; ?>
        };
    </script>
    <script src="../../assets/js/admin_chart.js"></script>
</body>
</html>