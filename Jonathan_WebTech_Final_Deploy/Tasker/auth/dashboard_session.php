<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    // Clear session and redirect to login
    $_SESSION = array();
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Optionally, you can add a simple role validation
if ($_SESSION['role'] !== 'user' && $_SESSION['role'] !== 'admin') {
    // Invalid role
    $_SESSION = array();
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>