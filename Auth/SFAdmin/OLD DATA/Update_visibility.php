<?php
ob_start();
include('header.php');

// Escape and sanitize input values from the URL
$COURSE = mysqli_real_escape_string($conn, $_GET['COURSE']);
$CERTIFICATE = mysqli_real_escape_string($conn, $_GET['CERTIFICATE']);
$STATUS = mysqli_real_escape_string($conn, $_GET['STATUS']);
$VISIBLE = mysqli_real_escape_string($conn, $_GET['VISIBLE']);
$TOPIC1 = mysqli_real_escape_string($conn, $_GET['TOPIC']); 
$ID = mysqli_real_escape_string($conn, $_GET['ID']);

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Prepare the redirect URL correctly
    $redirect = "Module_topics.php?COURSE=$COURSE&CERTIFICATE=$CERTIFICATE";

    // Select topic from the database
    $select_topic = mysqli_query($conn, "SELECT * FROM learning_topics WHERE topic_id='$ID'");
    if ($select_topic && mysqli_num_rows($select_topic) > 0) {
        $find_topic = mysqli_fetch_array($select_topic);
        $topic_visibility = $find_topic['topic_visibility'];

        // Toggle visibility status
        if ($topic_visibility == "Visible") {
            $topic_visibility1 = "Hidden";
        } else {
            $topic_visibility1 = "Visible";
        }

        // Update the visibility status
        $update_topic = mysqli_query($conn, "UPDATE learning_topics SET topic_visibility = '$topic_visibility1' WHERE topic_id = '$ID'");

        if ($update_topic) {
            // Redirect to the page after the update
            header("Location: $redirect");
            exit();
        } else {
            echo "Error updating topic visibility.";
        }
    } else {
        echo "Topic not found.";
    }
} else {
    echo "Invalid request method.";
}
?>
