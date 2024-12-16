<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/database.php";
require_once "../../auth/dashboard_session.php";
require_once "../../functions/task_functions.php";
require_once "../../functions/dashboard_process.php";
require_once "../../functions/load_stats.php";

# $user_id = $_SESSION['user_id'];
# $projects = getUserProjects($conn, $user_id);

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z"></script>
    <title>Dashboard - Tasker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
<?php if ($isAdmin): ?>
<!-- This should only be visible to admins -->
<nav class="admin-nav">
        <div class="admin-nav-container">
            <div class="admin-nav-menu">
                <h1 class="text-xl font-bold">Tasker Admin</h1>
                <div class="flex items-center gap-4">
                    <a href="../admin/admin_dashboard.php" class="admin-nav-link">Dashboard</a>
                    <a href="../admin/reports.php" class="admin-nav-link">Reports</a>
                    <a href="../admin/users.php" class="admin-nav-link">Users</a>
                    <a href="#" class="admin-nav-link active">User Dashboard</a>
                    <a href="../../auth/logout.php" class="admin-button admin-button-danger">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <div class="min-h-screen p-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold">Project Dashboard</h1>
                <div class="flex items-center gap-4">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="../../auth/logout.php" class="text-red-500 hover:text-red-700">Logout</a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Tasks Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Tasks</p>
                            <p class="text-2xl font-bold text-gray-900" data-stat="total"><?php echo $totalTasks; ?></p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Completed Tasks Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Completed Tasks</p>
                            <p class="text-2xl font-bold text-green-600" data-stat="completed"><?php echo $completedTasks; ?></p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- In Progress Tasks Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">In Progress</p>
                            <p class="text-2xl font-bold text-yellow-600" data-stat="in-progress"><?php echo $inProgressTasks; ?></p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Pending Tasks Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pending Tasks</p>
                            <p class="text-2xl font-bold text-red-600" data-stat="pending"><?php echo $pendingTasks; ?></p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Distribution Charts -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <h3 class="text-lg font-semibold mb-4">Task Distribution</h3>
    <div class="grid grid-cols-2 gap-8">
        <!-- Pie Chart -->
        <div class="h-64 relative">
            <canvas id="taskChart" 
                    data-completed="<?php echo $completedTasks; ?>"
                    data-in_progress="<?php echo $inProgressTasks; ?>"
                    data-pending="<?php echo $pendingTasks; ?>">
            </canvas>
        </div>
        <!-- Bar Chart -->
        <div class="h-64 relative">
            <canvas id="taskBarChart" 
                    data-completed="<?php echo $completedTasks; ?>"
                    data-in_progress="<?php echo $inProgressTasks; ?>"
                    data-pending="<?php echo $pendingTasks; ?>">
            </canvas>
        </div>
    </div>
</div>

            <!-- Project Creation Form -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-xl font-semibold mb-4">Create New Project</h2>
                <form method="POST" class="flex gap-4" name="project-form" id="project-form">
                    <input type="text" name="name" required placeholder="Project Name" 
                           class="flex-1 rounded-md border-gray-300 shadow-sm">
                    <input type="date" name="deadline" required 
                           class="rounded-md border-gray-300 shadow-sm" id="project-deadline">
                    <button type="submit" name="create_project" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                        Create Project
                    </button>
                </form>
                <div id="project-error" class="text-red-500 mt-2 hidden"></div>
            </div>

            <!-- Projects List -->
            <?php while ($project = $projects->fetch_assoc()): ?>
                <div class="bg-white p-6 rounded-lg shadow-md mb-6 project-card">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($project['name']); ?></h3>
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-500">
                                Deadline: <?php echo date('M d, Y', strtotime($project['deadline'])); ?>
                            </span>
                            <div class="flex items-center gap-2">
                                <button class="edit-project text-blue-500 hover:text-blue-700" 
                                        data-project-id="<?php echo $project['id']; ?>"
                                        data-project-name="<?php echo htmlspecialchars($project['name']); ?>"
                                        data-project-deadline="<?php echo $project['deadline']; ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button class="delete-project text-red-500 hover:text-red-700" 
                                        data-project-id="<?php echo $project['id']; ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Task Creation Form -->
                    <form method="POST" class="mb-4 flex gap-4 task-form" data-project-id="<?php echo $project['id']; ?>">
                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                        <input type="text" name="description" required placeholder="Task Description" 
                               class="flex-1 rounded-md border-gray-300 shadow-sm">
                        <input type="date" name="start_date" required 
                               class="rounded-md border-gray-300 shadow-sm task-start-date">
                        <input type="date" name="end_date" required 
                               class="rounded-md border-gray-300 shadow-sm task-end-date">
                        <button type="submit" name="create_task" 
                                class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors">
                            Add Task
                        </button>
                    </form>
                    <div class="task-error text-red-500 mb-4 hidden"></div>

                    <!-- Tasks List -->
                    <?php
                    $tasks = getProjectTasks($conn, $project['id']);
                    while ($task = $tasks->fetch_assoc()):
                    ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded mb-2 task-row highlight" 
                             data-task-id="<?php echo $task['id']; ?>">
                            <span><?php echo htmlspecialchars($task['description']); ?></span>
                            <div class="flex items-center gap-4">
                                <span class="text-sm text-gray-500">
                                    <?php echo date('M d', strtotime($task['start_date'])); ?> - 
                                    <?php echo date('M d', strtotime($task['end_date'])); ?>
                                </span>
                                <button class="edit-task text-blue-500 hover:text-blue-700"
                                        data-task-id="<?php echo $task['id']; ?>"
                                        data-task-description="<?php echo htmlspecialchars($task['description']); ?>"
                                        data-task-start="<?php echo $task['start_date']; ?>"
                                        data-task-end="<?php echo $task['end_date']; ?>">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                                <button class="delete-task text-red-500 hover:text-red-700"
                                        data-task-id="<?php echo $task['id']; ?>">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <form method="POST" class="flex items-center">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <select name="status" class="task-status rounded-md border-gray-300 shadow-sm">
                                        <option value="pending" <?php echo $task['status'] === 'pending' ? 'selected' : ''; ?>>
                                            Pending
                                        </option>
                                        <option value="in-progress" <?php echo $task['status'] === 'in-progress' ? 'selected' : ''; ?>>
                                            In Progress
                                        </option>
                                        <option value="completed" <?php echo $task['status'] === 'completed' ? 'selected' : ''; ?>>
                                            Completed
                                        </option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modals -->
    <!-- Edit Project Modal -->
    <div id="editProjectModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-xl font-semibold mb-4">Edit Project</h3>
            <form id="editProjectForm" class="space-y-4">
                <input type="hidden" name="project_id">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Project Name</label>
                    <input type="text" name="name" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Deadline</label>
                    <input type="date" name="deadline" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" class="modal-close px-4 py-2 text-gray-600">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div id="editTaskModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-xl font-semibold mb-4">Edit Task</h3>
            <form id="editTaskForm" class="space-y-4">
                <input type="hidden" name="task_id">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" name="description" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" class="modal-close px-4 py-2 text-gray-600">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/stats.js"></script>
    <script src="../../assets/js/task_func.js"></script>
    <script src="../../assets/js/edit_task.js"></script>
    <script src="../../assets/js/delete_task.js"></script>
</body>
</html>