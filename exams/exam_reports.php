<?php
include('../db.php');

// Get latest exam
$stmt = $conn->prepare("
    SELECT exam_id, title 
    FROM exams 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    die("No exam found.");
}

$exam_id = $exam['exam_id'];
$exam_title = $exam['title'];

// Get total marks
$stmt2 = $conn->prepare("
    SELECT SUM(marks) AS total_marks 
    FROM questions 
    WHERE exam_id = ?
");
$stmt2->bind_param("i", $exam_id);
$stmt2->execute();
$total = $stmt2->get_result()->fetch_assoc();
$total_marks = $total['total_marks'] ?? 0;

// Get players
$stmt3 = $conn->prepare("
    SELECT nickname, score, grade, school
    FROM players
    WHERE exam_id = ?
    ORDER BY score DESC
");
$stmt3->bind_param("i", $exam_id);
$stmt3->execute();
$players = $stmt3->get_result();


// ==============================
// FORCE DOWNLOAD
// ==============================
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=exam_report.csv");

$output = fopen("php://output", "w");

// Header row
fputcsv($output, [
    'Rank', 
    'Name', 
    'Score', 
    'Total Marks', 
    'Percentage', 
    'Grade', 
    'School', 
    'Status'
]);

$rank = 1;

while ($row = $players->fetch_assoc()) {

    $score = $row['score'];
    $percentage = ($total_marks > 0) ? round(($score / $total_marks) * 100) : 0;
    $status = ($percentage >= 50) ? 'Pass' : 'Fail';

    fputcsv($output, [
        $rank++,
        $row['nickname'],
        $score,
        $total_marks,
        $percentage . '%',
        $row['grade'],
        $row['school'],
        $status
    ]);
}

fclose($output);
exit();