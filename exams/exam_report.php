<?php
include('../db.php');

// ==============================
// FETCH LATEST EXAM
// ==============================
$stmt = $conn->prepare("
    SELECT exam_id, title, topic, grade, status 
    FROM exams 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo "No exam found.";
    exit();
}

$exam_id = $exam['exam_id'];
$exam_title = htmlspecialchars($exam['title']);
$exam_topic = htmlspecialchars($exam['topic']);
$exam_grade = htmlspecialchars($exam['grade']);
$exam_status = htmlspecialchars($exam['status']);


// ==============================
// FETCH PLAYERS
// ==============================
$stmt2 = $conn->prepare("
    SELECT nickname, score, grade, school
    FROM players
    WHERE exam_id = ?
    ORDER BY score DESC
");
$stmt2->bind_param("i", $exam_id);
$stmt2->execute();
$players = $stmt2->get_result();


// ==============================
// TOTAL MARKS
// ==============================
$stmt3 = $conn->prepare("
    SELECT SUM(marks) AS total_marks 
    FROM questions 
    WHERE exam_id = ?
");
$stmt3->bind_param("i", $exam_id);
$stmt3->execute();
$total = $stmt3->get_result()->fetch_assoc();
$total_marks = $total['total_marks'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Exam Reports</title>

<link rel="stylesheet" href="../dist/styles.css">

</head>

<body class="bg-gray-100 min-h-screen flex justify-center items-center">

<!-- ========================= -->
<!-- SIDEBAR -->
<!-- ========================= -->
<div class="flex flex-1">
    <?php include('./dynamic_side_bar.php'); ?>
</div>

<!-- ========================= -->
<!-- MAIN CONTENT -->
<!-- ========================= -->
<div class="flex-1 p-8">

<!-- HEADER -->
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Exam Dashboard</h1>
        <p class="text-gray-500 text-sm">Overview of latest exam and performance</p>
    </div>

    <div class="flex gap-3">
        <a href="examscreator.php"
        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg shadow">
        + Create Exam
        </a>

        <a href="export_reports.php"
        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg shadow">
        Download Excel
        </a>
    </div>
</div>


<!-- ========================= -->
<!-- EXAM INFO CARDS -->
<!-- ========================= -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

<div class="bg-white p-5 rounded-xl shadow-sm border">
    <p class="text-sm text-gray-500">Exam Title</p>
    <h2 class="font-bold text-lg text-gray-800"><?= $exam_title ?></h2>
</div>

<div class="bg-white p-5 rounded-xl shadow-sm border">
    <p class="text-sm text-gray-500">Topic</p>
    <h2 class="font-bold text-lg text-gray-800"><?= $exam_topic ?></h2>
</div>

<div class="bg-white p-5 rounded-xl shadow-sm border">
    <p class="text-sm text-gray-500">Grade</p>
    <h2 class="font-bold text-lg text-gray-800"><?= $exam_grade ?></h2>
</div>

<div class="bg-white p-5 rounded-xl shadow-sm border">
    <p class="text-sm text-gray-500">Total Marks</p>
    <h2 class="font-bold text-lg text-blue-600"><?= $total_marks ?></h2>
</div>

</div>


<!-- STATUS BAR -->
<div class="mb-6">
<span class="px-3 py-1 text-sm rounded-full font-medium
<?= $exam_status === 'active'
? 'bg-green-100 text-green-700'
: 'bg-gray-200 text-gray-600'
?>">
<?= ucfirst($exam_status) ?>
</span>
</div>


<!-- ========================= -->
<!-- LEADERBOARD TABLE -->
<!-- ========================= -->
<div class="bg-white rounded-xl shadow-sm border p-6">

<h2 class="text-lg font-bold text-gray-800 mb-4">
Leaderboard & Reports
</h2>

<?php if ($players->num_rows > 0): ?>

<div class="overflow-x-auto">
<table class="w-full text-sm">

<thead>
<tr class="text-left text-gray-500 border-b">
<th class="py-3">#</th>
<th class="py-3">Player</th>
<th class="py-3">Score</th>
<th class="py-3">%</th>
<th class="py-3">Grade</th>
<th class="py-3">School</th>
<th class="py-3">Status</th>
</tr>
</thead>

<tbody>

<?php 
$rank = 1;
while ($row = $players->fetch_assoc()): 

$score = $row['score'];
$percentage = ($total_marks > 0) ? round(($score / $total_marks) * 100) : 0;
$status = ($percentage >= 50) ? 'Pass' : 'Fail';
?>

<tr class="border-b hover:bg-gray-50 transition">

<td class="py-3 font-semibold"><?= $rank++ ?></td>

<td class="py-3"><?= htmlspecialchars($row['nickname']) ?></td>

<td class="py-3 font-semibold text-blue-600">
<?= $score ?> / <?= $total_marks ?>
</td>

<td class="py-3"><?= $percentage ?>%</td>

<td class="py-3"><?= htmlspecialchars($row['grade']) ?></td>

<td class="py-3"><?= htmlspecialchars($row['school']) ?></td>

<td class="py-3">
<span class="px-2 py-1 rounded text-xs font-medium
<?= $status === 'Pass'
? 'bg-green-100 text-green-700'
: 'bg-red-100 text-red-600'
?>">
<?= $status ?>
</span>
</td>

</tr>

<?php endwhile; ?>

</tbody>
</table>
</div>

<?php else: ?>

<div class="text-center py-10">
<p class="text-gray-400">No players yet.</p>
</div>

<?php endif; ?>

</div>

</div>

</body>
</html>