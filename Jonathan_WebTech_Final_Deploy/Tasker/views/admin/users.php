<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/database.php";
require_once "../../auth/dashboard_session.php";
require_once "../../auth/admin_check.php";
require_once "../../functions/admin/admin_functions.php";

// Verify admin access
requireAdmin($conn, $_SESSION['user_id']);

// Get users data
$users = getAllUsers($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z"></script>
    <title>User Management - Tasker Admin</title>
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
                    <a href="reports.php" class="admin-nav-link">Reports</a>
                    <a href="users.php" class="admin-nav-link active">Users</a>
                    <a href="../user/dashboard.php" class="admin-nav-link">User Dashboard</a>
                    <a href="../../auth/logout.php" class="admin-button admin-button-danger">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-8">
        <!-- User Stats -->
        <div class="stat-grid mb-8">
            <div class="stat-card">
                <p class="stat-label">Total Users</p>
                <p class="stat-value"><?php echo getTotalUsers($conn); ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Active Users</p>
                <p class="stat-value"><?php echo getActiveUsers($conn); ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Admins</p>
                <p class="stat-value"><?php echo getAdminCount($conn); ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">New Users (30 days)</p>
                <p class="stat-value"><?php echo getNewUsersCount($conn, 30); ?></p>
            </div>
        </div>

        <!-- User Management -->
        <div class="admin-card mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">User Management</h2>
                <button class="admin-button admin-button-primary" onclick="showAddUserModal()">
                    Add New User
                </button>
            </div>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Projects</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): 
                            $projectCount = getUserProjectCount($conn, $user['id']);
                            $lastActive = getLastUserActivity($conn, $user['id']);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $user['role'] === 'admin' ? 'status-admin' : 'status-user'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $lastActive < 7 ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $lastActive < 7 ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo $projectCount; ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="flex gap-2">
                                <button class="admin-button admin-button-secondary" 
                                        onclick="editUser(<?php echo $user['id']; ?>)">
                                    Edit
                                </button>
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                <button class="admin-button admin-button-danger" 
                                        onclick="deleteUser(<?php echo $user['id']; ?>)">
                                    Delete
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Activity -->
<div class="admin-card">
    <h2 class="text-xl font-semibold mb-6">Recent User Activity</h2>
    <div class="activity-feed">
        <?php 
        $activities = getUserActivities($conn);
        if (!empty($activities)):
            foreach ($activities as $activity): 
        ?>
            <div class="activity-item">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="font-medium"><?php echo htmlspecialchars($activity['username']); ?></span>
                        <span><?php echo htmlspecialchars($activity['action']); ?></span>
                    </div>
                    <span class="activity-meta">
                        <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                    </span>
                </div>
            </div>
        <?php 
            endforeach;
        else:
        ?>
            <div class="text-gray-500 text-center py-4">No recent activity found</div>
        <?php endif; ?>
    </div>
</div>

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="modal hidden">
        <div class="admin-card max-w-md w-full mx-auto">
            <h3 class="text-xl font-semibold mb-4" id="modalTitle">Add New User</h3>
            <form id="userForm" class="space-y-4">
                <input type="hidden" name="user_id" id="userId">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" id="username" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <select name="role" id="role" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="password-fields">
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" class="admin-button admin-button-secondary" 
                            onclick="closeModal()">Cancel</button>
                    <button type="submit" class="admin-button admin-button-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/admin/user_functions.js"></script>
</body>
</html>