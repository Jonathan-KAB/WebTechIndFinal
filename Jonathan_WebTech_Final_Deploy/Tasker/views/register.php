<?php
require_once "../auth/register_user.php";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Tasker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold mb-6">Register</h2>
            <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <?php 
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>
            <form method="POST" class="space-y-4" onsubmit="return validateRegister()">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" id="username" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <span id="usernameError" class="text-red-500 text-sm hidden"></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <span id="emailError" class="text-red-500 text-sm hidden"></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <span id="passwordError" class="text-red-500 text-sm hidden"></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirmPassword" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <span id="confirmPasswordError" class="text-red-500 text-sm hidden"></span>
                </div>
                <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
                    Register
                </button>
            </form>
            <p class="mt-4 text-center">
                Already have an account? <a href="login.php" class="text-blue-500">Login</a>
            </p>
        </div>
    </div>
    <script src="../assets/js/register.js"></script>
    <script src="../assets/js/password_view.js"></script>
</body>
</html>