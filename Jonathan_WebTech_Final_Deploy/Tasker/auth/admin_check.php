<?php
function isAdmin($conn, $userId) {
    $sql = "SELECT role FROM users WHERE id = ? AND role = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function requireAdmin($conn, $userId) {
    if (!isAdmin($conn, $userId)) {
        header("Location: ../dashboard.php");
        exit();
    }
}
?>