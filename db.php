<?php
$servername = "localhost";
$username = "u664421868_blisdatabase";
$password = "Blisdata@1234";
$dbname = "u664421868_blisdatabase";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>