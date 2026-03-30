<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'u664421868_report');
define('DB_PASS', 'Flavikan@1983');
define('DB_NAME', 'u664421868_report');

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>