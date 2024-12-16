<?php
require_once '../../config/database.php';
require_once '../../auth/admin_check.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No user ID provided']);
    exit;
}

$id = $conn->real_escape_string($_GET['id']);

$sql = "SELECT id, username, email, role FROM users WHERE id = '$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'User not found']);
}
?>