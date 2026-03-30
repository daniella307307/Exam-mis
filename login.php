<?php
session_start();
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email_address']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $hashed_password = md5($password);

    // Query to fetch the user
    $sql = "SELECT user_id, firstname, lastname, email_address, phone_number, password, status, access_level, school_ref, user_group_ref, Password_tocken 
            FROM users 
            WHERE email_address = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Verify password
        if ($hashed_password===$row['lastname']) {
            // Set session variables
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['firstname'] = $row['firstname'];
            $_SESSION['lastname'] = $row['lastname'];
            // Redirect to a protected page
            header("Location: welcome.php");
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that email address.";
    }
}
?>
