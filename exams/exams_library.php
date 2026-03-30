<?php
//getting the logged in user (host) id
// session_start();
// $host_id = $_SESSION['host_id'] ?? 0;
// if (!$host_id) {
//     header("Location: login.php");
//     exit();
// }
include('../db.php');

// Fetch the latest exam created by this host
$stmt = $conn->prepare("SELECT * FROM exams ORDER BY created_at DESC LIMIT 1");
// $stmt->bind_param("i", $host_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();
if (!$exam) {
    echo "No exam found. Please create an exam first.";
    exit();
}
$exam_id = $exam['exam_id'];
//DISPLAY ALL THE EXAM DETAILS
$exam_title = htmlspecialchars($exam['title']);
$exam_topic = htmlspecialchars($exam['topic']);
$exam_grade = htmlspecialchars($exam['grade']);
$exam_status = htmlspecialchars($exam['status']);

// You can now use these variables to display the exam details on the page

?>

<!Doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Details</title>
    <link rel="stylesheet" href="../dist/styles.css">
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="text-right mb-4">
         <a href="examscreator.php" class="px-4 py-2 bg-green-600 text-white rounded mb-6 inline-block text-center">
             Create New Exam
         </a>
    </div>
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md" href="edit_exam.php?exam_id=<?= $exam_id ?>">
        <h1 class="text-2xl font-bold mb-4"><?= $exam_title ?></h1>
        <p><strong>Topic:</strong> <?= $exam_topic ?></p>
        <p><strong>Grade:</strong> <?= $exam_grade ?></p>
        <p><strong>Status:</strong> <?= $exam_status ?></p>
        <a href="edit_exam.php?exam_id=<?= $exam_id ?>" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded">Edit Exam</a>
    </div>
</body>
</html>
