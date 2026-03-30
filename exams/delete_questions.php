<?php
include('../db.php');

$question_id = $_GET['question_id'] ?? 0;
$exam_id = $_GET['exam_id'] ?? 0;

if ($question_id) {
    $conn->query("DELETE FROM options WHERE question_id=$question_id");
    $conn->query("DELETE FROM questions WHERE question_id=$question_id");
}

header("Location: edit_exam.php?exam_id=$exam_id");
exit();