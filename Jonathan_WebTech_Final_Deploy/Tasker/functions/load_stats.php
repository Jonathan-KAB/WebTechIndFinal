<?php
# session_start();
require_once '../../config/database.php';
require_once 'task_functions.php';

function getStats($conn, $userId) {
   $totalTasks = 0;
   $completedTasks = 0;
   $pendingTasks = 0;
   $inProgressTasks = 0;

   $allProjects = getUserProjects($conn, $userId);
   while ($project = $allProjects->fetch_assoc()) {
       $projectTasks = getProjectTasks($conn, $project['id']);
       while ($task = $projectTasks->fetch_assoc()) {
           $totalTasks++;
           switch ($task['status']) {
               case 'completed':
                   $completedTasks++;
                   break;
               case 'pending':
                   $pendingTasks++;
                   break;
               case 'in-progress':
                   $inProgressTasks++;
                   break;
           }
       }
   }

   return [
       'totalTasks' => $totalTasks,
       'completedTasks' => $completedTasks,
       'pendingTasks' => $pendingTasks,
       'inProgressTasks' => $inProgressTasks
   ];
}

if (isset($_POST['ajax']) && isset($_SESSION['user_id'])) {
   header('Content-Type: application/json');
   echo json_encode(getStats($conn, $_SESSION['user_id']));
   exit;
} else if (isset($_SESSION['user_id'])) {
   $stats = getStats($conn, $_SESSION['user_id']);
   extract($stats);
} else {
   $totalTasks = $completedTasks = $pendingTasks = $inProgressTasks = 0;
}

# if (!isset($_POST['ajax'])) {
#     var_dump($stats);  // Temporary debug
# }

?>