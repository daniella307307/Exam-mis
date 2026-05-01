<?php
session_start();
include("../db.php");

$exam_id   = (int) ($_SESSION['exam_id']   ?? $_GET['eid'] ?? 0);
$player_id = (int) ($_SESSION['player_id'] ?? $_GET['pid'] ?? 0);

if (!$exam_id) {
    header("Location: join_exam.php");
    exit();
}

$_SESSION['exam_id']   = $exam_id;
if ($player_id) { $_SESSION['player_id'] = $player_id; }

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

// Close connection - all data fetched
if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
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
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

body{
  font-family:'Nunito',sans-serif;
  background:linear-gradient(135deg,#0d0d2b,#1e1b4b);
  color:#f1f5f9;
  min-height:100vh;
  padding:24px 20px 60px;
  display:flex;flex-direction:column;align-items:center;
  position:relative;overflow-x:hidden;
}
body::before,body::after{
  content:'';position:fixed;border-radius:50%;
  filter:blur(100px);opacity:.18;pointer-events:none;z-index:0;
}
body::before{width:500px;height:500px;background:#7c3aed;top:-150px;right:-150px}
body::after{width:400px;height:400px;background:#06b6d4;bottom:-150px;left:-150px}
body > *{position:relative;z-index:1}

/* Header */
.header{
  width:100%;max-width:600px;
  display:flex;justify-content:space-between;align-items:center;
  margin-bottom:20px;
}
.title{
  font-weight:900;font-size:18px;
  background:linear-gradient(135deg,#a855f7,#06b6d4);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.badge{
  font-size:11px;padding:5px 12px;border-radius:99px;
  font-weight:800;letter-spacing:1px;text-transform:uppercase;
}
.live{
  background:rgba(239,68,68,.15);
  color:#fca5a5;
  border:1px solid rgba(239,68,68,.4);
}
.live::before{
  content:'';display:inline-block;width:7px;height:7px;border-radius:50%;
  background:#ef4444;margin-right:6px;vertical-align:middle;
  animation:pulse 1.4s ease-in-out infinite;
}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.7)}}
.finished{
  background:rgba(34,197,94,.15);
  color:#86efac;
  border:1px solid rgba(34,197,94,.4);
}

/* Hero */
.hero{text-align:center;margin-bottom:24px}
.hero h1{
  font-size:clamp(28px,5vw,40px);font-weight:900;
  background:linear-gradient(135deg,#facc15,#f97316);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  margin-bottom:6px;
}
.hero p{font-size:14px;color:#94a3b8}

/* Winner banner */
.winner{
  width:100%;max-width:600px;
  background:linear-gradient(135deg,rgba(250,204,21,.18),rgba(249,115,22,.12));
  border:1px solid rgba(250,204,21,.4);
  border-radius:14px;padding:18px;text-align:center;
  margin-bottom:20px;font-weight:800;font-size:15px;color:#fde68a;
  box-shadow:0 12px 30px rgba(250,204,21,.12);
}

/* My score (current player) */
.my-score{
  width:100%;max-width:600px;
  background:linear-gradient(135deg,rgba(124,58,237,.35),rgba(168,85,247,.18));
  border:1px solid rgba(168,85,247,.45);
  backdrop-filter:blur(20px);
  border-radius:14px;padding:18px 20px;
  display:flex;justify-content:space-between;align-items:center;
  margin-bottom:24px;
  box-shadow:0 16px 40px rgba(124,58,237,.25);
}
.my-score > div:first-child > div:first-child{
  font-size:11px;color:#cbd5e1;letter-spacing:1.5px;
  text-transform:uppercase;font-weight:700;margin-bottom:4px;
}
.nickname{font-weight:800;font-size:17px;color:#f1f5f9}
.points{
  font-size:30px;font-weight:900;
  background:linear-gradient(135deg,#facc15,#f97316);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

/* Board */
.board{
  width:100%;max-width:600px;
  display:flex;flex-direction:column;gap:10px;
}
.row{
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.08);
  backdrop-filter:blur(20px);
  border-radius:12px;padding:14px 16px;
  display:flex;align-items:center;gap:14px;
  transition:transform .15s,border-color .15s;
}
.row:hover{transform:translateX(4px);border-color:rgba(168,85,247,.3)}
.row:nth-child(1){
  background:linear-gradient(135deg,rgba(250,204,21,.12),rgba(255,255,255,.05));
  border-color:rgba(250,204,21,.3);
}
.row:nth-child(2){
  background:linear-gradient(135deg,rgba(148,163,184,.12),rgba(255,255,255,.05));
  border-color:rgba(148,163,184,.3);
}
.row:nth-child(3){
  background:linear-gradient(135deg,rgba(251,146,60,.12),rgba(255,255,255,.05));
  border-color:rgba(251,146,60,.3);
}
.rank{
  font-weight:900;font-size:18px;width:36px;text-align:center;color:#f1f5f9;
}
.avatar{
  width:40px;height:40px;border-radius:50%;
  background:linear-gradient(135deg,#7c3aed,#06b6d4);
  display:flex;align-items:center;justify-content:center;
  font-weight:900;font-size:14px;color:#fff;
}
.name{flex:1;font-weight:700;font-size:15px;color:#e2e8f0}
.score{font-weight:900;font-size:16px;color:#facc15}

.empty-state{
  text-align:center;color:#94a3b8;padding:30px;
  background:rgba(255,255,255,.03);
  border:1px dashed rgba(255,255,255,.1);
  border-radius:12px;
}

/* Footer */
.footer{
  margin-top:28px;font-size:13px;color:#94a3b8;
  text-align:center;
}
a{
  text-decoration:none;font-weight:800;color:#a855f7;
  transition:color .15s;
}
a:hover{color:#c084fc;text-decoration:underline}
</style>
</head>

<body>
    <?php $back_to = 'index.php'; $back_label = 'Home'; include('nav_back.php'); ?>

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
<div>Your score</div>
<div class="nickname"><?= htmlspecialchars($me['nickname']) ?></div>
</div>

<div class="points">
<?= number_format($me['score']) ?>
</div>
</div>
<?php endif; ?>

<div class="board">

<?php if(empty($leaders)): ?>

<div class="empty-state">
⏳ Waiting for players...
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
Refreshing every 5 seconds &nbsp;·&nbsp; <a href="results.php">View My Results</a>
<?php else: ?>
<a href="results.php">View My Results &amp; Certificate</a>
<?php endif; ?>

</div>

<script>
// Stop the browser back button from taking the player back to the
// game-pin entry / running game once they've reached the leaderboard.
// We rewrite the current history entry and intercept any popstate.
(function blockBackNavigation() {
    try {
        history.replaceState({ leaderboard: true }, '', window.location.href);
        window.addEventListener('popstate', () => {
            history.pushState({ leaderboard: true }, '', window.location.href);
        });
        history.pushState({ leaderboard: true }, '', window.location.href);
    } catch (e) { /* best effort */ }
})();
</script>
</body>
</html>