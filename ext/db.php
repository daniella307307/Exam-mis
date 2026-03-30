<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "blisdb";



 //$conn = mysqli_connect('localhost', 'root', '','blisglob_app');
 //$conn = mysqli_connect('localhost', 'blisglob_root', 'hthNm]_LVu;=','blisglob_db_blis_global');

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>