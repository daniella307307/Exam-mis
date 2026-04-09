<?php
$servername = "193.203.168.143";
$port = "3306";
$username = "u664421868_blisdatabase";
$password = "Blisdata@1234";
$dbname = "u664421868_blisdatabase";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>