<?php
session_start();

$exam_id = (int)($_GET['eid'] ?? $_SESSION['exam_id'] ?? 0);
$player_id = (int)($_GET['pid'] ?? $_SESSION['player_id'] ?? 0);

if (!$exam_id || !$player_id) {
    header("Location: join_exam.php");
    exit();
}

include("../db.php");

$stmt = $conn->prepare("SELECT title FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();
$exam_title = $exam['title'] ?? 'Exam';

$ps = $conn->prepare("SELECT nickname FROM players WHERE player_id = ?");
$ps->bind_param("i", $player_id);
$ps->execute();
$student_name = $ps->get_result()->fetch_assoc()['nickname'] ?? 'Student';

if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Exam Submitted | <?= htmlspecialchars($exam_title) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{
    font-family:'Nunito',sans-serif;
    background:linear-gradient(135deg,#0d0d2b,#1e1b4b);
    min-height:100vh;display:flex;align-items:center;justify-content:center;
    padding:20px;color:#f1f5f9;
}
.card{
    background:rgba(255,255,255,0.05);
    border:1px solid rgba(168,85,247,0.3);
    backdrop-filter:blur(20px);
    border-radius:24px;padding:50px 40px;max-width:520px;width:100%;
    text-align:center;box-shadow:0 25px 80px rgba(0,0,0,0.5);
}
.icon{
    width:100px;height:100px;border-radius:50%;
    background:linear-gradient(135deg,#22c55e,#16a34a);
    display:flex;align-items:center;justify-content:center;
    margin:0 auto 24px;font-size:50px;color:white;
    animation:pop 0.6s cubic-bezier(0.68,-0.55,0.265,1.55);
    box-shadow:0 10px 40px rgba(34,197,94,0.4);
}
@keyframes pop{0%{transform:scale(0)}70%{transform:scale(1.1)}100%{transform:scale(1)}}
h1{font-size:32px;font-weight:900;margin-bottom:12px;
   background:linear-gradient(135deg,#facc15,#f97316);
   -webkit-background-clip:text;-webkit-text-fill-color:transparent}
.subtitle{color:#cbd5e1;font-size:16px;margin-bottom:24px;line-height:1.6}
.exam-name{color:#a855f7;font-weight:700}
.info-box{
    background:rgba(124,58,237,0.15);
    border:1px solid rgba(168,85,247,0.3);
    border-radius:12px;padding:18px;margin:24px 0;
    font-size:14px;line-height:1.6;color:#e2e8f0;text-align:left;
}
.info-box strong{color:#facc15;display:block;margin-bottom:6px;font-size:13px;letter-spacing:1px;text-transform:uppercase}
.btn{
    display:block;width:100%;padding:16px;margin-top:14px;
    background:linear-gradient(135deg,#7c3aed,#a855f7);
    color:white;text-decoration:none;border-radius:12px;
    font-size:16px;font-weight:900;
    transition:transform 0.15s,box-shadow 0.15s;
    box-shadow:0 8px 24px rgba(124,58,237,0.4);
}
.btn:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(124,58,237,0.5)}
.btn-secondary{
    background:transparent;border:2px solid rgba(168,85,247,0.4);color:#cbd5e1;
    box-shadow:none;
}
.btn-secondary:hover{background:rgba(124,58,237,0.15)}
.student{font-size:14px;color:#94a3b8;margin-top:20px}
</style>
</head>
<body>
<div class="card">
    <div class="icon">✓</div>
    <h1>Submission Received!</h1>
    <p class="subtitle">
        Great work, <strong style="color:#a5f3fc"><?= htmlspecialchars($student_name) ?></strong>!<br>
        Your practical exam <span class="exam-name"><?= htmlspecialchars($exam_title) ?></span> 
        has been submitted successfully.
    </p>

    <div class="info-box">
        <strong>📌 What happens next?</strong>
        Your teacher will review your practical work and grade it manually. 
        You will be notified once your results are ready. Check back later to see your score.
    </div>

    <a href="join_exam.php" class="btn">Take Another Exam</a>
    <a href="../Auth/SF/index.php" class="btn btn-secondary">← Back to Dashboard</a>

    <p class="student">Submitted at <?= date('h:i A · M d, Y') ?></p>
</div>
</body>
</html>