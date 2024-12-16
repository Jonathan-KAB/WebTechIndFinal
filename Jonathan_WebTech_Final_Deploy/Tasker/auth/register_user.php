<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    
    // Check if email already exists
    $checkEmail = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($checkEmail);
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "This email is already registered.";
        header("Location: ../views/register.php");
        exit();
    }

    // Password strength server-side validation
    $password = $_POST['password'];
    if (strlen($password) < 8 || 
        !preg_match("/[A-Z]/", $password) || 
        !preg_match("/[a-z]/", $password) || 
        !preg_match("/[0-9]/", $password) || 
        !preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
        $_SESSION['error'] = "Password does not meet security requirements.";
        header("Location: ../register.php");
        exit();
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashedPassword')";
    if ($conn->query($sql)) {
        $_SESSION['message'] = "Registration successful. Please login.";
        header("Location: ../../views/login.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: ../../views/register.php");
        exit();
    }
}
?>