<?php
session_start();
include("../db.php");

if (!isset($_SESSION['exam_id'])) {
    header("Location: join_exam.php");
    exit();
}

$exam_id   = (int) $_SESSION['exam_id'];
$player_id = (int) ($_SESSION['player_id'] ?? 0);

/* Fetch exam */
$stmt = $conn->prepare("SELECT title, status FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

/* Top 10 players */
$lstmt = $conn->prepare("
    SELECT nickname, score
    FROM players
    WHERE exam_id = ?
    ORDER BY score DESC
    LIMIT 10
");
$lstmt->bind_param("i", $exam_id);
$lstmt->execute();
$leaders = $lstmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* Current player */
$me = null;
if ($player_id) {
    $mstmt = $conn->prepare("SELECT nickname, score FROM players WHERE player_id = ?");
    $mstmt->bind_param("i", $player_id);
    $mstmt->execute();
    $me = $mstmt->get_result()->fetch_assoc();
}

$is_finished = ($exam['status'] ?? '') === 'finished';
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Leaderboard</title>

<?php if (!$is_finished): ?>
<meta http-equiv="refresh" content="5">
<?php endif; ?>

<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">

<style>

*{
box-sizing:border-box;
margin:0;
padding:0;
}

body{
font-family:'Nunito',sans-serif;
background:#f7f9fc;
color:#1e293b;
padding:20px;
display:flex;
flex-direction:column;
align-items:center;
}

/* Header */

.header{
width:100%;
max-width:600px;
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

.title{
font-weight:900;
font-size:18px;
}

.badge{
font-size:12px;
padding:4px 10px;
border-radius:20px;
font-weight:700;
}

.live{
background:#fee2e2;
color:#dc2626;
}

.finished{
background:#dcfce7;
color:#15803d;
}

/* Hero */

.hero{
text-align:center;
margin-bottom:20px;
}

.hero h1{
font-size:32px;
font-weight:900;
}

.hero p{
font-size:14px;
color:#64748b;
}

/* My score */

.my-score{
width:100%;
max-width:600px;
background:white;
border:1px solid #e2e8f0;
border-radius:12px;
padding:15px;
display:flex;
justify-content:space-between;
margin-bottom:20px;
}

.nickname{
font-weight:800;
}

.points{
font-size:26px;
font-weight:900;
color:#7c3aed;
}

/* Board */

.board{
width:100%;
max-width:600px;
display:flex;
flex-direction:column;
gap:10px;
}

.row{
background:white;
border:1px solid #e2e8f0;
border-radius:10px;
padding:12px;
display:flex;
align-items:center;
gap:10px;
}

.rank{
font-weight:900;
width:30px;
text-align:center;
}

.avatar{
width:36px;
height:36px;
border-radius:50%;
background:#ede9fe;
display:flex;
align-items:center;
justify-content:center;
font-weight:900;
}

.name{
flex:1;
font-weight:700;
}

.score{
font-weight:900;
}

/* Winner */

.winner{
width:100%;
max-width:600px;
background:#ecfdf5;
border:1px solid #bbf7d0;
border-radius:12px;
padding:15px;
text-align:center;
margin-bottom:20px;
font-weight:700;
}

/* Footer */

.footer{
margin-top:20px;
font-size:13px;
color:#64748b;
}

a{
text-decoration:none;
font-weight:700;
color:#7c3aed;
}

</style>
</head>

<body>

<div class="header">
<div class="title"><?= htmlspecialchars($exam['title']) ?></div>

<?php if(!$is_finished): ?>
<div class="badge live">LIVE</div>
<?php else: ?>
<div class="badge finished">FINISHED</div>
<?php endif; ?>
</div>

<?php if ($is_finished && !empty($leaders)): ?>
<div class="winner">
🏆 <?= htmlspecialchars($leaders[0]['nickname']) ?> wins with <?= number_format($leaders[0]['score']) ?> pts
</div>
<?php endif; ?>

<div class="hero">
<h1>Leaderboard</h1>
<p>Top players</p>
</div>

<?php if($me): ?>
<div class="my-score">
<div>
<div style="font-size:12px;color:#64748b;">Your score</div>
<div class="nickname"><?= htmlspecialchars($me['nickname']) ?></div>
</div>

<div class="points">
<?= number_format($me['score']) ?>
</div>
</div>
<?php endif; ?>

<div class="board">

<?php if(empty($leaders)): ?>

<div style="text-align:center;color:#64748b;padding:20px;">
Waiting for players...
</div>

<?php else: ?>

<?php foreach($leaders as $i => $row): ?>

<?php
$pos = $i+1;
$initial = strtoupper(substr($row['nickname'],0,2));
?>

<div class="row">

<div class="rank">
<?= $pos <=3 ? ["🥇","🥈","🥉"][$i] : $pos ?>
</div>

<div class="avatar">
<?= htmlspecialchars($initial) ?>
</div>

<div class="name">
<?= htmlspecialchars($row['nickname']) ?>
</div>

<div class="score">
<?= number_format($row['score']) ?> pts
</div>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>

<div class="footer">

<?php if(!$is_finished): ?>
Refreshing every 5 seconds
<?php else: ?>
<a href="join_exam.php">Play another game</a>
<?php endif; ?>

</div>

</body>
</html>