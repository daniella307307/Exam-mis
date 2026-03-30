<?php
session_start();
include('header.php');

if (isset($_POST['Update_profile'])) {
    // File upload configuration
    $targetDir = "../profiles/";
    $fileName = basename($_FILES['profile_picture']['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowedTypes = array('jpg', 'jpeg', 'png');

    // Check file type
    if (in_array($fileType, $allowedTypes)) {
        // Get list of existing files
        $existingFiles = glob($targetDir . '*.{jpg,jpeg,png}', GLOB_BRACE);

        // Remove the uploaded file name from the list
        $key = array_search($targetFilePath, $existingFiles);
        if ($key !== false) {
            unset($existingFiles[$key]);
        }

        // Delete similar existing files
        foreach ($existingFiles as $file) {
            // Example criteria: delete files with the same name prefix
            if (pathinfo($file, PATHINFO_FILENAME) == pathinfo($targetFilePath, PATHINFO_FILENAME)) {
                unlink($file);
            }
        }

        // Upload the new file
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            // Update the database
            $userId = $details_user['user_id'];
            $updateQuery = "UPDATE users SET user_image = '$fileName' WHERE user_id = $userId";
            $update = mysqli_query($conn, $updateQuery);

            if ($update) {
                echo "Profile picture updated successfully.";
            } else {
                echo "Database update failed: " . mysqli_error($conn);
            }
        } else {
            echo "There was an error uploading the file.";
        }
    } else {
        echo "Invalid file type. Only JPG, JPEG, and PNG files are allowed.";
    }
}
?>