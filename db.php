<?php
// Auto-detect environment
$is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);

if ($is_local) {
    $servername = "193.203.168.143";  // Remote DB from Ubuntu
    $port = 3306;
} else {
    $servername = "localhost";  // Hostinger uses local DB
    $port = 3306;
}

$username = "u664421868_blisdatabase";
$password = "Blisdata@1234";
$dbname = "u664421868_blisdatabase";

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>