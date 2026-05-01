<?php
header("Content-Type: application/json");
include('../../db.php');

// Get total exams
$exams_result = $conn->query("SELECT COUNT(*) as count FROM exams");
$total_exams = $exams_result->fetch_assoc()['count'];

// Get active exams
$active_result = $conn->query("SELECT COUNT(*) as count FROM exams WHERE status = 'active'");
$active_exams = $active_result->fetch_assoc()['count'];

// Get total students who participated
$students_result = $conn->query("SELECT COUNT(DISTINCT nickname) as count FROM players");
$total_students = $students_result->fetch_assoc()['count'];

// Get total answers submitted
$answers_result = $conn->query("SELECT COUNT(*) as count FROM answers");
$total_answers = $answers_result->fetch_assoc()['count'];

echo json_encode([
    "success" => true,
    "total_exams" => $total_exams,
    "active_exams" => $active_exams,
    "total_students" => $total_students,
    "total_answers" => $total_answers
]);
?>
