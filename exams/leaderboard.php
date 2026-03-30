<?php
session_start();
include("../db.php");

if (!isset($_SESSION['exam_id'])) {
    header("Location: join_exam.php");
    exit();
}

$exam_id   = (int) $_SESSION['exam_id'];
$player_id = (int) ($_SESSION['player_id'] ?? 0);

// Fetch exam
$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

// Fetch top 10 players by score
$lstmt = $conn->prepare("
    SELECT nickname, score,
           RANK() OVER (ORDER BY score DESC) AS position
    FROM players
    WHERE exam_id = ?
    ORDER BY score DESC
    LIMIT 10
");
$lstmt->bind_param("i", $exam_id);
$lstmt->execute();
$leaders = $lstmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Current player rank & score
$me = null;
if ($player_id) {
    $mstmt = $conn->prepare("
        SELECT nickname, score,
               RANK() OVER (ORDER BY score DESC) AS position
        FROM players WHERE exam_id = ?
        ORDER BY score DESC
    ");
    // Simpler: just get own row
    $mstmt = $conn->prepare("SELECT nickname, score FROM players WHERE player_id = ?");
    $mstmt->bind_param("i", $player_id);
    $mstmt->execute();
    $me = $mstmt->get_result()->fetch_assoc();
}

// Medal emojis
$medals = ['🥇','🥈','🥉'];

// Is game finished?
$is_finished = ($exam['status'] ?? '') === 'finished';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leaderboard — <?= htmlspecialchars($exam['title']) ?></title>
<?php if (!$is_finished): ?>
  <meta http-equiv="refresh" content="5">
<?php endif; ?>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800;900&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:       #0a0a18;
    --surface:  #13132b;
    --card:     #1a1a35;
    --accent:   #f59e0b;
    --accent2:  #fbbf24;
    --purple:   #7c3aed;
    --text:     #f5f4ff;
    --muted:    #7c7a9e;
    --gold:     #f59e0b;
    --silver:   #94a3b8;
    --bronze:   #cd7c2f;
    --border:   rgba(255,255,255,0.07);
    --green:    #22c55e;
  }

  body {
    font-family: 'Nunito', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0 16px 48px;
    position: relative;
    overflow-x: hidden;
  }

  /* Top bar */
  .topbar {
    width: 100%;
    max-width: 600px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 0 8px;
    margin-bottom: 8px;
  }
  .topbar .game-name {
    font-size: 15px;
    font-weight: 800;
    color: var(--muted);
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }
  .live-badge {
    display: flex; align-items: center; gap: 6px;
    background: rgba(239,68,68,0.15);
    border: 1px solid rgba(239,68,68,0.3);
    color: #f87171;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 20px;
  }
  .live-dot {
    width: 7px; height: 7px;
    background: #ef4444;
    border-radius: 50%;
    animation: blink 1.2s ease-in-out infinite;
  }
  @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.2} }

  /* Hero title */
  .hero {
    text-align: center;
    margin-bottom: 32px;
    animation: fadeDown 0.5s ease both;
  }
  @keyframes fadeDown {
    from { opacity:0; transform: translateY(-16px); }
    to   { opacity:1; transform: translateY(0); }
  }
  .hero h1 {
    font-size: clamp(28px, 6vw, 42px);
    font-weight: 900;
    letter-spacing: -1px;
    background: linear-gradient(135deg, #fff 20%, var(--accent2) 80%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1.1;
  }
  .hero p { color: var(--muted); font-size: 15px; margin-top: 6px; }

  /* My score card */
  .my-score {
    width: 100%;
    max-width: 600px;
    background: linear-gradient(135deg, rgba(124,58,237,0.2), rgba(245,158,11,0.12));
    border: 1px solid rgba(245,158,11,0.3);
    border-radius: 18px;
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    animation: fadeDown 0.55s ease both 0.1s;
    gap: 12px;
  }
  .my-score .label { font-size: 13px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing:0.06em; }
  .my-score .nickname { font-size: 20px; font-weight: 900; margin-top: 2px; }
  .my-score .pts {
    font-size: 32px;
    font-weight: 900;
    color: var(--accent2);
    white-space: nowrap;
  }
  .my-score .pts span { font-size: 14px; color: var(--muted); font-weight: 700; margin-left:2px; }

  /* Leaderboard list */
  .board {
    width: 100%;
    max-width: 600px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .board-row {
    display: flex;
    align-items: center;
    gap: 14px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 14px 20px;
    position: relative;
    overflow: hidden;
    animation: slideIn 0.4s cubic-bezier(.22,1,.36,1) both;
    transition: transform 0.2s;
  }
  .board-row:hover { transform: translateX(4px); }

  /* Staggered animations */
  .board-row:nth-child(1) { animation-delay: 0.05s; }
  .board-row:nth-child(2) { animation-delay: 0.10s; }
  .board-row:nth-child(3) { animation-delay: 0.15s; }
  .board-row:nth-child(4) { animation-delay: 0.20s; }
  .board-row:nth-child(5) { animation-delay: 0.25s; }
  .board-row:nth-child(n+6) { animation-delay: 0.30s; }

  @keyframes slideIn {
    from { opacity:0; transform: translateX(-24px); }
    to   { opacity:1; transform: translateX(0); }
  }

  /* Top 3 accents */
  .board-row.rank-1 { border-color: rgba(245,158,11,0.45); background: linear-gradient(135deg, #1a1a35 60%, rgba(245,158,11,0.08)); }
  .board-row.rank-2 { border-color: rgba(148,163,184,0.3); background: linear-gradient(135deg, #1a1a35 60%, rgba(148,163,184,0.06)); }
  .board-row.rank-3 { border-color: rgba(205,124,47,0.3);  background: linear-gradient(135deg, #1a1a35 60%, rgba(205,124,47,0.06)); }

  .rank-num {
    font-size: 22px;
    font-weight: 900;
    min-width: 36px;
    text-align: center;
    color: var(--muted);
  }
  .rank-1 .rank-num { color: var(--gold); }
  .rank-2 .rank-num { color: var(--silver); }
  .rank-3 .rank-num { color: var(--bronze); }

  .avatar {
    width: 42px; height: 42px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    font-weight: 900;
    flex-shrink: 0;
    background: rgba(124,58,237,0.25);
    border: 2px solid rgba(124,58,237,0.3);
    text-transform: uppercase;
  }
  .rank-1 .avatar { background: rgba(245,158,11,0.2); border-color: rgba(245,158,11,0.5); }
  .rank-2 .avatar { background: rgba(148,163,184,0.15); border-color: rgba(148,163,184,0.4); }
  .rank-3 .avatar { background: rgba(205,124,47,0.15); border-color: rgba(205,124,47,0.4); }

  .player-name {
    flex: 1;
    font-size: 17px;
    font-weight: 800;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .score-bar-wrap {
    flex: 1.4;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .score-bar-bg {
    height: 6px;
    background: rgba(255,255,255,0.07);
    border-radius: 99px;
    overflow: hidden;
  }
  .score-bar-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, var(--purple), var(--accent2));
    transition: width 0.8s cubic-bezier(.22,1,.36,1);
  }
  .rank-1 .score-bar-fill { background: linear-gradient(90deg, #d97706, var(--gold)); }
  .rank-2 .score-bar-fill { background: linear-gradient(90deg, #64748b, #94a3b8); }
  .rank-3 .score-bar-fill { background: linear-gradient(90deg, #92400e, #cd7c2f); }

  .score-pts {
    font-size: 19px;
    font-weight: 900;
    min-width: 64px;
    text-align: right;
    color: var(--text);
  }
  .score-pts small { font-size: 12px; color: var(--muted); font-weight: 700; }

  /* Empty state */
  .empty { text-align: center; color: var(--muted); padding: 48px 0; font-size: 16px; }
  .empty .icon { font-size: 48px; margin-bottom: 12px; }

  /* Refreshing indicator */
  .refresh-note {
    margin-top: 28px;
    color: var(--muted);
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    animation: fadeDown 0.6s ease both 0.5s;
  }
  .spin { animation: rotate 1.5s linear infinite; display: inline-block; }
  @keyframes rotate { to { transform: rotate(360deg); } }

  /* Confetti for winner */
  .confetti-bar {
    width: 100%;
    max-width: 600px;
    background: linear-gradient(135deg, rgba(34,197,94,0.15), rgba(245,158,11,0.12));
    border: 1px solid rgba(34,197,94,0.3);
    border-radius: 18px;
    padding: 20px 24px;
    text-align: center;
    margin-bottom: 24px;
    animation: fadeDown 0.5s ease both;
  }
  .confetti-bar h2 { font-size: 22px; font-weight: 900; color: var(--green); }
  .confetti-bar p  { color: var(--muted); font-size: 14px; margin-top: 4px; }

  @media (max-width: 480px) {
    .score-bar-wrap { display: none; }
    .board-row { padding: 12px 16px; }
  }
</style>
</head>
<body>

<div class="topbar">
  <span class="game-name"><?= htmlspecialchars($exam['title']) ?></span>
  <?php if (!$is_finished): ?>
    <span class="live-badge"><span class="live-dot"></span>Live</span>
  <?php else: ?>
    <span class="live-badge" style="background:rgba(34,197,94,0.1);border-color:rgba(34,197,94,0.3);color:#4ade80;">🏁 Final</span>
  <?php endif; ?>
</div>

<?php if ($is_finished && !empty($leaders)): ?>
<div class="confetti-bar">
  <h2>🎉 Game Over!</h2>
  <p><?= htmlspecialchars($leaders[0]['nickname']) ?> wins with <?= number_format($leaders[0]['score']) ?> points!</p>
</div>
<?php endif; ?>

<div class="hero">
  <h1>🏆 Leaderboard</h1>
  <p>Top players right now</p>
</div>

<?php if ($me): ?>
<div class="my-score">
  <div>
    <div class="label">Your score</div>
    <div class="nickname">👤 <?= htmlspecialchars($me['nickname']) ?></div>
  </div>
  <div class="pts"><?= number_format($me['score']) ?><span>pts</span></div>
</div>
<?php endif; ?>

<div class="board">

<?php if (empty($leaders)): ?>
  <div class="empty">
    <div class="icon">⏳</div>
    <p>Waiting for players to score…</p>
  </div>

<?php else:
  // Find max score for bar width calculation
  $max_score = max(array_column($leaders, 'score')) ?: 1;

  foreach ($leaders as $i => $row):
    $pos      = $i + 1;
    $cls      = $pos <= 3 ? "rank-{$pos}" : "";
    $medal    = $medals[$i] ?? $pos;
    $initials = mb_strtoupper(mb_substr($row['nickname'], 0, 2));
    $pct      = round($row['score'] / $max_score * 100);
?>
  <div class="board-row <?= $cls ?>">
    <div class="rank-num"><?= $pos <= 3 ? $medal : $pos ?></div>
    <div class="avatar"><?= htmlspecialchars($initials) ?></div>
    <div class="player-name"><?= htmlspecialchars($row['nickname']) ?></div>
    <div class="score-bar-wrap">
      <div class="score-bar-bg">
        <div class="score-bar-fill" style="width:<?= $pct ?>%"></div>
      </div>
    </div>
    <div class="score-pts"><?= number_format($row['score']) ?><br><small>pts</small></div>
  </div>
<?php endforeach; endif; ?>

</div>

<?php if (!$is_finished): ?>
<p class="refresh-note">
  <span class="spin">↻</span> Refreshing every 5 seconds
</p>
<?php else: ?>
<p class="refresh-note" style="justify-content:center; margin-top:28px;">
  <a href="join_exam.php" style="color: var(--accent2); font-weight:800; text-decoration:none;">← Play another game</a>
</p>
<?php endif; ?>

</body>
</html>