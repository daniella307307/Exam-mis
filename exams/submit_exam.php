<?php
session_start();
include("../db.php");

if (!isset($_SESSION['exam_id'], $_SESSION['player_id'])) {
    header("Location: join_exam.php");
    exit();
}

$exam_id   = (int) $_SESSION['exam_id'];
$player_id = (int) $_SESSION['player_id'];

// Fetch questions
$qstmt = $conn->prepare("
    SELECT q.question_id, q.marks
    FROM questions q
    WHERE q.exam_id = ?
");
$qstmt->bind_param("i", $exam_id);
$qstmt->execute();
$questions = $qstmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total_score = 0;

foreach ($questions as $q) {

    $qid = $q['question_id'];

    // selected option
    $selected_option_id = (int) ($_POST["q{$qid}"] ?? 0);

    // check correctness + get option text in ONE query
    $check = $conn->prepare("
        SELECT option_text, is_correct
        FROM options
        WHERE option_id = ? AND question_id = ?
    ");
    $check->bind_param("ii", $selected_option_id, $qid);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();

    $chosen = $result['option_text'] ?? '';
    $is_correct = $result ? (int)$result['is_correct'] : 0;

    // USE MARKS (IMPORTANT FIX)
    $points = $is_correct ? (int)$q['marks'] : 0;
    $total_score += $points;

    // save answer
    $ins = $conn->prepare("
        INSERT INTO answers 
        (player_id, exam_id, question_id, chosen_answer, is_correct, points_earned)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            chosen_answer = VALUES(chosen_answer),
            is_correct = VALUES(is_correct),
            points_earned = VALUES(points_earned)
    ");

    $ins->bind_param(
        "iiisii",
        $player_id,
        $exam_id,
        $qid,
        $chosen,
        $is_correct,
        $points
    );

    $ins->execute();
}

// update score
$upd = $conn->prepare("UPDATE players SET score = ? WHERE player_id = ?");
$upd->bind_param("ii", $total_score, $player_id);
$upd->execute();

header("Location: leaderboard.php");
exit();
?>