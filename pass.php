<?php
$plain_password = "123456";
$stored_hashed_password = '$2y$10$e0NRfsJd.rJlBpdsNl1/LOmPEoG7p8fUAYGnXXNQJb.g9VVk/EuAi'; // Example hash from database

if (password_verify($plain_password, $stored_hashed_password)) {
    echo "Password is valid!";
} else {
    echo "Invalid password.";
}
?>
