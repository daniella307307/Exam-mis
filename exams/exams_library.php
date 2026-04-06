<?php
include('../db.php');
$stmt = $conn->prepare("SELECT exam_id, title, topic, grade, status FROM exams ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo "No exam found. Please create an exam first.";
    exit();
}

$exam_id = $exam['exam_id'];
$exam_title = htmlspecialchars($exam['title']);
$exam_topic = htmlspecialchars($exam['topic']);
$exam_grade = htmlspecialchars($exam['grade']);
$exam_status = htmlspecialchars($exam['status']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Exam Details</title>

<link rel="stylesheet" href="../dist/styles.css">

</head>

<body class="bg-gray-50 min-h-screen flex flex-col items-center p-6">

<!-- Top Bar -->
 

<div class="w-full max-w-xl flex justify-between items-center mb-6">

<h1 class="text-lg font-bold text-gray-700">
Latest Exam
</h1>

<a href="examscreator.php"
class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg shadow-sm">
Create Exam
</a>

</div>


<!-- Exam Card -->

<div class="w-full max-w-xl bg-white rounded-xl shadow-sm border border-gray-200 p-6">

<h2 class="text-2xl font-bold text-gray-800 mb-4">
<?= $exam_title ?>
</h2>

<div class="space-y-2 text-gray-600">

<p>
<span class="font-semibold text-gray-700">Topic:</span>
<?= $exam_topic ?>
</p>

<p>
<span class="font-semibold text-gray-700">Grade:</span>
<?= $exam_grade ?>
</p>

<p>
<span class="font-semibold text-gray-700">Status:</span>

<span class="px-2 py-1 text-sm rounded
<?= $exam_status === 'active'
? 'bg-green-100 text-green-700'
: 'bg-gray-100 text-gray-600'
?>">
<?= ucfirst($exam_status) ?>
</span>

</p>

</div>


<!-- Action Buttons -->

<div class="mt-6 flex gap-3">

<a href="edit_exam.php?exam_id=<?= $exam_id ?>"
class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm shadow-sm">
Edit Exam
</a>

</div>

</div>

</body>
</html>