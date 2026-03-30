<?php
// Registration script
include('db.php');
// Get the password from the form
$plain_password = "123456";

// Hash the password
$firstname="Mika";
$lastname ="Yunusu";
$email  ="yunusumika@gmail.com";

$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// Store $hashed_password in the database
// Example query (assuming connection is established and other fields are set):
$sql = "INSERT INTO users (firstname, lastname, email_address, password) VALUES ('$firstname', '$lastname', '$email', '$hashed_password')";

if (mysqli_query($conn, $sql)) {
    echo "User registered successfully.";
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

mysqli_close($conn);
?>
