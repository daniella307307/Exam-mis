<?php
session_start();
include("../db.php");

$exam_id   = (int)($_SESSION['exam_id']   ?? $_GET['eid'] ?? 0);
$player_id = (int)($_SESSION['player_id'] ?? $_GET['pid'] ?? 0);

if (!$exam_id || !$player_id) {
    header("Location: join_exam.php");
    exit();
}

$vchk = $conn->prepare("SELECT 1 FROM players WHERE player_id = ? AND exam_id = ?");
$vchk->bind_param("ii", $player_id, $exam_id);
$vchk->execute();
if (!$vchk->get_result()->fetch_row()) {
    header("Location: join_exam.php");
    exit();
}

$_SESSION['exam_id']   = $exam_id;
$_SESSION['player_id'] = $player_id;

$exam = $conn->query("SELECT title FROM exams WHERE exam_id=$exam_id")->fetch_assoc();

$res = $conn->query(
    "SELECT nickname, score, player_id FROM players
     WHERE exam_id=$exam_id ORDER BY score DESC LIMIT 50"
);
$players = [];
while ($row = $res->fetch_assoc()) { $players[] = $row; }

// Determine player rank and score FIRST
$myRank = 0; $myScore = 0;
foreach ($players as $i => $p) {
    if ((int)$p['player_id'] === $player_id) {
        $myRank = $i + 1; $myScore = (int)$p['score'];
    }
}

// Now calculate percentage after $myScore is known
$mstmt = $conn->prepare("SELECT SUM(marks) AS total_marks FROM questions WHERE exam_id = ?");
$mstmt->bind_param("i", $exam_id);
$mstmt->execute();
$total_marks = (int)($mstmt->get_result()->fetch_assoc()['total_marks'] ?? 0);
$percentage = $total_marks > 0 ? round(($myScore / $total_marks) * 100) : 0;

// Check if this player has a certificate (only if table exists)
$cert_code = null;
$ct_chk = $conn->query("SHOW TABLES LIKE 'student_certificates'");
if ($ct_chk && $ct_chk->num_rows > 0) {
    $cert_stmt = $conn->prepare("SELECT cert_code FROM student_certificates WHERE player_id = ? AND exam_id = ?");
    $cert_stmt->bind_param("ii", $player_id, $exam_id);
    $cert_stmt->execute();
    $cert_row = $cert_stmt->get_result()->fetch_assoc();
    $cert_code = $cert_row['cert_code'] ?? null;
}

$medals = ['🥇','🥈','🥉'];

$qres = $conn->query(
    "SELECT a.chosen_answer, o.is_correct, a.points_earned, q.question_text
     FROM answers a JOIN options o ON a.chosen_answer = o.option_id
     JOIN questions q ON a.question_id = q.question_id
     WHERE a.player_id=$player_id AND a.exam_id=$exam_id ORDER BY a.answer_id"
);
$answers = [];
while ($row = $qres->fetch_assoc()) { $answers[] = $row; }
$correct_count = array_sum(array_column($answers,'is_correct'));
$total_q = count($answers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Results</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0d0d2b;--card:#16163a;--accent:#7c3aed;--accent2:#a855f7;--green:#22c55e;--yellow:#facc15;--red:#ef4444;--text:#f1f5f9;--muted:#94a3b8;--radius:16px}
body{font-family:'Nunito',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:24px 20px 60px;overflow-x:hidden;position:relative}
body::before{content:'';position:fixed;width:500px;height:500px;border-radius:50%;filter:blur(100px);opacity:.18;background:var(--accent);top:-150px;right:-150px;pointer-events:none}
h1.page-title{font-size:clamp(20px,4vw,28px);font-weight:900;text-align:center;margin-bottom:4px}
.page-sub{text-align:center;color:var(--muted);font-size:14px;margin-bottom:28px}
.hero{background:linear-gradient(135deg,rgba(124,58,237,.6),rgba(168,85,247,.3));border:1px solid rgba(168,85,247,.4);border-radius:var(--radius);padding:28px 24px;text-align:center;margin-bottom:20px}
.hero-rank{font-size:64px;font-weight:900;line-height:1}
.hero-label{font-size:12px;color:rgba(255,255,255,.6);margin:4px 0 16px;letter-spacing:1px;text-transform:uppercase}
.hero-score{font-size:52px;font-weight:900;background:linear-gradient(135deg,#facc15,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-score-label{font-size:11px;color:rgba(255,255,255,.5);letter-spacing:1px;text-transform:uppercase;margin-top:4px}
.hero-accuracy{display:inline-block;margin-top:16px;background:rgba(255,255,255,.1);border-radius:99px;padding:6px 20px;font-size:14px;font-weight:700;color:#a5f3fc}
.stats{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:24px}
.stat-card{background:var(--card);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:16px 12px;text-align:center}
.stat-val{font-size:26px;font-weight:900}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-top:4px}
.section-title{font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);margin-bottom:12px}
.lb-list{display:flex;flex-direction:column;gap:8px;margin-bottom:28px}
.lb-row{display:flex;align-items:center;gap:12px;background:var(--card);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:12px 16px}
.lb-row.me{border-color:var(--accent2);background:rgba(124,58,237,.2)}
.lb-row:nth-child(1){border-color:rgba(250,204,21,.4);background:rgba(250,204,21,.07)}
.lb-row:nth-child(2){background:rgba(148,163,184,.06)}
.lb-row:nth-child(3){background:rgba(251,146,60,.06)}
.lb-rank{font-size:20px;font-weight:900;min-width:36px;text-align:center}
.lb-nick{flex:1;font-size:15px;font-weight:700}
.lb-score{font-size:18px;font-weight:900;color:var(--yellow)}
.breakdown{display:flex;flex-direction:column;gap:8px}
.ans-row{background:var(--card);border-radius:12px;padding:14px 16px;border-left:4px solid}
.ans-row.correct{border-color:var(--green)}.ans-row.wrong{border-color:var(--red)}
.ans-q{font-size:13px;color:var(--muted);margin-bottom:6px}
.ans-bottom{display:flex;justify-content:space-between;align-items:center}
.ans-choice{font-size:15px;font-weight:700}
.ans-pts{font-size:13px;font-weight:900;color:var(--yellow)}
.play-again{display:block;width:100%;margin-top:32px;padding:16px;background:linear-gradient(135deg,var(--accent),var(--accent2));border:none;border-radius:12px;color:#fff;font-family:'Nunito',sans-serif;font-size:18px;font-weight:900;cursor:pointer;text-align:center;text-decoration:none;transition:transform .15s,box-shadow .15s;box-shadow:0 8px 24px rgba(124,58,237,.4)}
.play-again:hover{transform:translateY(-2px)}
.cert-btn{display:block;width:100%;margin-top:16px;padding:16px;background:linear-gradient(135deg,#d97706,#f59e0b);border-radius:12px;color:#fff;font-family:'Nunito',sans-serif;font-size:17px;font-weight:900;text-align:center;text-decoration:none;transition:transform .15s,box-shadow .15s;box-shadow:0 8px 24px rgba(245,158,11,.4)}
.cert-btn:hover{transform:translateY(-2px)}
.confetti-piece{position:fixed;top:-10px;border-radius:2px;opacity:0;animation:fall linear forwards}
@keyframes fall{0%{opacity:1;transform:translateY(0) rotate(0deg)}100%{opacity:0;transform:translateY(110vh) rotate(720deg)}}
</style>
</head>
<body>
  <?php $back_to = 'index.php'; $back_label = 'Home'; include('nav_back.php'); ?>

<?php if ($myRank > 0 && $myRank <= 3): ?>
<script>
(function(){
  const colors=['#a855f7','#facc15','#22c55e','#ef4444','#3b82f6','#f97316'];
  for(let i=0;i<70;i++){
    const el=document.createElement('div');
    el.className='confetti-piece';
    const size=6+Math.random()*8;
    el.style.cssText=`left:${Math.random()*100}vw;background:${colors[i%colors.length]};width:${size}px;height:${size}px;animation-duration:${2+Math.random()*3}s;animation-delay:${Math.random()*2}s;border-radius:${Math.random()>.5?'50%':'2px'}`;
    document.body.appendChild(el);
  }
})();
</script>
<?php endif; ?>

<h1 class="page-title">Game Over!</h1>
<p class="page-sub"><?= htmlspecialchars($exam['title'] ?? '') ?></p>

<div class="hero">
  <div class="hero-rank"><?= ($myRank <= 3 && $myRank > 0) ? $medals[$myRank-1] : ($myRank > 0 ? "#$myRank" : '–') ?></div>
  <div class="hero-label">Your ranking</div>
  <div class="hero-score"><?= number_format($myScore) ?> / <?= $total_marks ?></div>
  <div class="hero-score-label">Score &nbsp;·&nbsp; <?= $percentage ?>%</div>
  <div class="hero-accuracy">
    <?= $total_q > 0 ? round(($correct_count/$total_q)*100) : 0 ?>% accuracy &nbsp;·&nbsp; <?= $correct_count ?>/<?= $total_q ?> correct
  </div>
</div>

<div class="stats">
  <div class="stat-card"><div class="stat-val"><?= $myRank > 0 ? "#$myRank" : '–' ?></div><div class="stat-lbl">Rank</div></div>
  <div class="stat-card"><div class="stat-val"><?= $correct_count ?></div><div class="stat-lbl">Correct</div></div>
  <div class="stat-card"><div class="stat-val"><?= $percentage ?>%</div><div class="stat-lbl">Score</div></div>
</div>

<?php if ($cert_code): ?>
<a href="certificate.php?code=<?= urlencode($cert_code) ?>" class="cert-btn">
  🏅 View &amp; Print Your Certificate
</a>
<?php endif; ?>

<div class="section-title">Leaderboard</div>
<div class="lb-list">
<?php foreach ($players as $i => $p):
  $rank = $i+1; $isMe = (int)$p['player_id'] === $player_id;
  $medal = $rank<=3 ? $medals[$rank-1] : $rank;
?>
  <div class="lb-row <?= $isMe?'me':'' ?>">
    <span class="lb-rank"><?= $medal ?></span>
    <span class="lb-nick"><?= htmlspecialchars($p['nickname']) ?><?= $isMe?' (you)':'' ?></span>
    <span class="lb-score"><?= number_format($p['score']) ?></span>
  </div>
<?php endforeach; ?>
</div>

<?php if ($answers): ?>
<div class="section-title">Your answers</div>
<div class="breakdown">
<?php foreach ($answers as $a):
  $cls=$a['is_correct']?'correct':'wrong'; $ico=$a['is_correct']?'✅':'❌';
?>
  <div class="ans-row <?= $cls ?>">
    <div class="ans-q"><?= htmlspecialchars($a['question_text']) ?></div>
    <div class="ans-bottom">
      <span class="ans-choice"><?= $ico ?> <?= htmlspecialchars($a['chosen_answer']?:'(no answer)') ?></span>
      <?php if($a['is_correct']): ?><span class="ans-pts">+<?= number_format($a['points_earned']) ?> pts</span><?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<a href="index.php" class="play-again">Play Again</a>

</body>
</html>
<?php $conn->close(); ?>
