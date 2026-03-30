<?php
include('../db.php'); 
 
// Assuming you have a connection to your database
// Replace with your actual database connection code
 

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$bank_id = intval($_GET['bank_id']);

$sql = "SELECT 	acount_number, acount_bank,acount_id   FROM bank_account  WHERE acount_bank=$bank_id";
$result = $conn->query($sql);

$accounts = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $accounts[$row['acount_id']] = $row['acount_number'];
    }
}

echo json_encode($accounts);

$conn->close();
?>
