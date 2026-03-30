<?php
ob_start();
include('header.php');

// Make sure $conn and $school_ref are defined before using them
// Example: $school_ref = $_SESSION['school_ref']; (if stored in session)

// Sanitize GET parameters
$CURRENT = isset($_GET['CURRENT']) ? mysqli_real_escape_string($conn, $_GET['CURRENT']) : '';
$COURSE     = isset($_GET['COURSE']) ? mysqli_real_escape_string($conn, $_GET['COURSE']) : '';
$CERTIFICATE= isset($_GET['CERTIFICATE']) ? mysqli_real_escape_string($conn, $_GET['CERTIFICATE']) : '';
$STATUS     = isset($_GET['STATUS']) ? mysqli_real_escape_string($conn, $_GET['STATUS']) : '';
$VISIBLE    = isset($_GET['VISIBLE']) ? mysqli_real_escape_string($conn, $_GET['VISIBLE']) : '';
$TOPIC1     = isset($_GET['TOPIC']) ? mysqli_real_escape_string($conn, $_GET['TOPIC']) : '';
$ID   = isset($_GET['ID']) ? mysqli_real_escape_string($conn, $_GET['ID']) : '';





//COURSE=1&CERTIFICATE=1&CURRENT=Active&STATUS=Active&ID=2

// Validate required fields
if (empty($COURSE) || empty($CERTIFICATE) || empty($ID) || empty($TOPIC1)) {
    echo "Missing required data.";
     echo "COURSE:".$COURSE;
      echo "CERTIFICATE:".$CERTIFICATE;
       echo "ID:".$ID;
        echo "Topic:".$TOPIC1;
    
    
    
    
    
    exit();
}

// Only proceed if GET request
if ($_SERVER["REQUEST_METHOD"] == "GET") {

    // Prepare the redirect URL
    $redirect = "Module_topics.php?COURSE=$COURSE&CERTIFICATE=$CERTIFICATE";

    // Check if the topic already exists in the database
    $select_topic = mysqli_query($conn, "SELECT * FROM topics_management 
        WHERE management_cert='$CERTIFICATE' 
        AND management_course='$COURSE' 
        AND management_topic='$ID' 
        AND management_school='$school_ref'");

    if (mysqli_num_rows($select_topic) <1) {
        // Topic already exists — insert new one (toggle behavior)
        $insert_topic = mysqli_query($conn, "INSERT INTO topics_management 
            (management_id, management_cert, management_course, management_topic, management_school) 
            VALUES (NULL, '$CERTIFICATE', '$COURSE', '$TOPIC1', '$school_ref')");

        if ($insert_topic) {
             header("Location: $redirect");
            exit();
        } else {
            echo "❌ Error inserting topic.";
        }
    } else {
        // Topic does not exist — delete the current one
        $delete_topic = mysqli_query($conn, "DELETE FROM topics_management 
            WHERE management_cert='$CERTIFICATE' 
            AND management_course='$COURSE' 
            AND management_topic='$ID' 
            AND management_school='$school_ref'");

        if ($delete_topic) {
           header("Location: $redirect");
            exit();
        } else {
            echo "❌ Error deleting topic.";
        }
    }

} else {
    echo "❌ Invalid request method.";
}
?>
