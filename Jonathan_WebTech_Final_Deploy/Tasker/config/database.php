<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'jonathan.boateng');
define('DB_PASS', 'removed_for_security_reasons');
define('DB_NAME', 'webtech_fall2024_jonathan_boateng');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
   # echo "Connection successful";
}

?>
