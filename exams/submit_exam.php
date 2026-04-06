<?php
session_start();
include("../db.php");
 
if (!isset($_SESSION['exam_id'], $_SESSION['player_id'])) {
    header("Location: join_exam.php");
    exit();
}
 
$exam_id   = (int) $_SESSION['exam_id'];
$player_id = (int) $_SESSION['player_id'];
 
// Fetch all questions for this exam and their correct options
$qstmt = $conn->prepare("
    SELECT q.question_id,
           o.option_text AS correct_answer
    FROM questions q
    JOIN options o ON o.question_id = q.question_id AND o.is_correct = 1
    WHERE q.exam_id = ?
");
$qstmt->bind_param("i", $exam_id);
$qstmt->execute();
$questions = $qstmt->get_result()->fetch_all(MYSQLI_ASSOC);
 
$total_score = 0;
 
foreach ($questions as $q) {
    $qid    = $q['question_id'];
    $chosen = trim($_POST["q{$qid}"] ?? '');
    $is_correct = (strtolower($chosen) === strtolower($q['correct_answer'])) ? 1 : 0;
    $points     = $is_correct ? 100 : 0;   // flat scoring; add time bonus here later
    $total_score += $points;
 
    $ins = $conn->prepare("
        INSERT INTO answers (player_id, exam_id, question_id, chosen_answer, is_correct, points_earned)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            chosen_answer  = VALUES(chosen_answer),
            is_correct     = VALUES(is_correct),
            points_earned  = VALUES(points_earned)
    ");
    $ins->bind_param("iiisii", $player_id, $exam_id, $qid, $chosen, $is_correct, $points);
    $ins->execute();
}
 
// Update player score
$upd = $conn->prepare("UPDATE players SET score = ? WHERE player_id = ?");
$upd->bind_param("ii", $total_score, $player_id);
$upd->execute();
 
header("Location: leaderboard.php");
exit();
?>
*/
 