<?php
include('Access.php');

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access!");
}

$ID = $_GET['ID'];

$select_file = mysqli_query($conn, "SELECT topic_document FROM learning_topics WHERE topic_id='$ID'");
$File_found = mysqli_fetch_array($select_file);
$filePath = $File_found['topic_document'];

if (!file_exists($filePath) || pathinfo($filePath, PATHINFO_EXTENSION) !== 'pdf') {
    die("File not found or invalid format.");
}

// Prevent download by forcing inline view
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename='document.pdf'");
readfile($filePath);
?>
