<?php
session_start();
include('../db.php');

if (!isset($_SESSION['exam_id'])) {
    header("Location: index.php");
    exit();
}


$exam_id   = $_SESSION['exam_id'];
$player_id = $_SESSION['player_id'];

// Fetch exam details
$stmt = $conn->prepare("SELECT title, start_time, status FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

// Handle group info submission
$groupSaved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_group'])) {
    $mode    = $_SESSION['mode']    ?? 'individual';
    $school  = trim($_POST['school']  ?? '');
    $grade   = trim($_POST['grade']   ?? '');
   $membersArray = $_POST['members'] ?? [];
$membersArray = array_map('trim', $membersArray); // clean each name
$membersArray = array_filter($membersArray); // remove empty ones

$members = implode(", ", $membersArray); // convert to string
    // Make sure your players table has: mode VARCHAR(20), school VARCHAR(255),
    // grade VARCHAR(100), group_members TEXT
    $upd = $conn->prepare(
        "UPDATE players SET mode=?, school=?, grade=?, group_members=? WHERE player_id=?"
    );
    $upd->bind_param("ssssi", $mode, $school, $grade, $members, $player_id);
    $upd->execute();
    $groupSaved = true;
}

$start_time = strtotime($exam['start_time']);

// Close connection - all data fetched
if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Waiting Room – <?= htmlspecialchars($exam['title']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --accent:#7c3aed;
  --accent2:#a855f7;
  --text:#f1f5f9;
  --muted:#94a3b8;
  --radius:20px
}
body{
  font-family:'Nunito',sans-serif;
  background:linear-gradient(135deg,#0d0d2b,#1e1b4b);
  color:var(--text);
  min-height:100vh;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  padding:24px;
  overflow-x:hidden;
  position:relative;
}
body::before,body::after{
  content:'';position:fixed;border-radius:50%;
  filter:blur(100px);opacity:.25;pointer-events:none;
  animation:drift 8s ease-in-out infinite alternate;
}
body::before{width:500px;height:500px;background:var(--accent);top:-150px;left:-120px}
body::after{width:420px;height:420px;background:#06b6d4;bottom:-150px;right:-120px;animation-delay:-4s}
@keyframes drift{to{transform:translate(40px,30px)}}

.logo{font-size:36px;font-weight:900;letter-spacing:-1px;margin-bottom:4px;
  background:linear-gradient(135deg,#a855f7,#06b6d4);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.tagline{color:var(--muted);font-size:13px;margin-bottom:28px}

.card{
  background:rgba(255,255,255,.05);
  border:1px solid rgba(168,85,247,.3);
  backdrop-filter:blur(20px);
  -webkit-backdrop-filter:blur(20px);
  border-radius:var(--radius);
  padding:40px 36px;
  width:100%;max-width:480px;
  box-shadow:0 25px 80px rgba(0,0,0,.5);
  position:relative;z-index:1;
}

.badge{
  background:rgba(124,58,237,.1);
  border:1px solid rgba(124,58,237,.25);
  color:var(--accent);
}
.badge::before{
  content:'';width:8px;height:8px;border-radius:50%;
  background:#a855f7;animation:pulse 1.5s ease-in-out infinite;
}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.8)}}

.exam-title{
  font-size:24px;font-weight:900;margin-bottom:6px;text-align:center;
  background:linear-gradient(135deg,#facc15,#f97316);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.player-name{color:var(--muted);font-size:14px;margin-bottom:24px;text-align:center}
.player-name span{color:#a855f7;font-weight:800}

.countdown-wrap{
  background:rgba(124,58,237,.15);
  border:1px solid rgba(168,85,247,.3);
  border-radius:14px;
  padding:24px;
  text-align:center;
  margin-bottom:8px;
}
.countdown-label{font-size:11px;font-weight:700;text-transform:uppercase;
  letter-spacing:1.5px;color:var(--muted);margin-bottom:10px}
.countdown{font-size:52px;font-weight:900;letter-spacing:4px;
  background:linear-gradient(135deg,#a855f7,#06b6d4);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

.divider{
  height:1px;
  background:rgba(255,255,255,.08);
  margin:24px 0;
}
.toggle-label{font-size:11px;font-weight:700;letter-spacing:1px;
  text-transform:uppercase;color:var(--muted);margin-bottom:10px;display:block}
.toggle-row{display:flex;gap:10px;margin-bottom:20px}
.toggle-btn{
  flex:1;padding:12px;border-radius:12px;border:1.5px solid rgba(255,255,255,.12);
  background:rgba(255,255,255,.04);color:var(--muted);font-family:'Nunito',sans-serif;
  font-size:14px;font-weight:700;cursor:pointer;transition:.2s;
}
.toggle-btn.active{
  background:rgba(124,58,237,.25);border-color:#a855f7;color:#c084fc;
}

.group-fields{display:none;animation:fadeIn .3s ease}
.group-fields.visible{display:block}
@keyframes fadeIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}

.field{margin-bottom:16px}
label.lbl{display:block;font-size:11px;font-weight:700;letter-spacing:1px;
  text-transform:uppercase;color:var(--muted);margin-bottom:7px}
input[type=text],textarea{
  width:100%;
  background:rgba(15,15,40,.6);
  border:1.5px solid rgba(168,85,247,.25);
  border-radius:12px;
  padding:12px 16px;
  font-size:15px;
  font-family:'Nunito',sans-serif;
  font-weight:600;
  color:#f1f5f9;
  outline:none;
  transition:border-color .2s,background .2s;
}

input[type=text]:focus,textarea:focus{
  border-color:var(--accent);
  background:rgba(15,15,40,.85);
  color:#f1f5f9;
}
input[type=text]::placeholder,textarea::placeholder{color:var(--muted);font-weight:400}

.btn-save{
  width:100%;padding:14px;
  background:linear-gradient(135deg,var(--accent),var(--accent2));
  border:none;border-radius:12px;color:#fff;
  font-family:'Nunito',sans-serif;font-size:16px;font-weight:900;
  cursor:pointer;transition:transform .15s,box-shadow .15s;
  box-shadow:0 8px 24px rgba(124,58,237,.4);
}
.btn-save:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(124,58,237,.5)}
.btn-save:active{transform:translateY(0)}

.success{
  background:rgba(34,197,94,.1);
  border:1px solid rgba(34,197,94,.25);
  border-radius:10px;
  padding:10px 14px;
  font-size:13px;
  color:#16a34a;
  margin-bottom:16px;
  text-align:center;
}
.players-online{
  display:flex;align-items:center;justify-content:center;gap:10px;
  color:var(--muted);font-size:14px;margin-top:8px;
}
.players-online strong{
  color:#facc15;font-size:18px;font-weight:900;
  padding:2px 12px;border-radius:99px;
  background:rgba(250,204,21,.12);
  border:1px solid rgba(250,204,21,.3);
}
select{
  width:100%;
  padding:12px;
  border-radius:10px;
  border:1px solid rgba(0,0,0,.1);
  background:#f1f5f9;
  font-weight:700;
}

textarea{
  width:100%;
  padding:12px;
  border-radius:10px;
  border:1px solid rgba(0,0,0,.1);
  background:#f1f5f9;
}
</style>
</head>
<body>


<div class="card">

  <div style="text-align:center">

  </div>

  <div class="exam-title"><?= htmlspecialchars($exam['title']) ?></div>
  <div class="player-name">
    Playing as <span><?= htmlspecialchars($_SESSION['nickname']) ?></span>
  </div>

  <div class="countdown-wrap">
    <div class="countdown-label">Game starts in</div>
    <div class="countdown" id="timer">--:--</div>
  </div>

  <div class="divider"></div>

  <div class="players-online">
    🎮 Players joined: <strong id="playerCount">–</strong>
  </div>

</div>

<script>
const EXAM_ID   = <?= (int)$exam_id ?>;
const startTime = <?= $start_time ? ($start_time * 1000) : 0 ?>;

// ── Countdown timer ──────────────────────────────────────────────
function updateTimer() {
  const el = document.getElementById('timer');
  if (!startTime) { el.textContent = 'Soon'; return; }

  const diff = startTime - Date.now();
  if (diff <= 0) { el.textContent = 'Starting…'; return; }

  const totalSec = Math.floor(diff / 1000);
  const h = Math.floor(totalSec / 3600);
  const m = Math.floor((totalSec % 3600) / 60);
  const s = totalSec % 60;
  const pad = n => String(n).padStart(2, '0');
  el.textContent = (h > 0 ? pad(h) + ':' : '') + pad(m) + ':' + pad(s);
}
setInterval(updateTimer, 1000);
updateTimer();

// ── Poll live player count ───────────────────────────────────────
function pollPlayers() {
  fetch('getplayercount.php?eid=' + EXAM_ID, { credentials: 'same-origin' })
    .then(r => r.json())
    .then(d => { document.getElementById('playerCount').textContent = d.count ?? '–'; })
    .catch(() => {});
}
setInterval(pollPlayers, 5000);
pollPlayers();

// ── Poll for host pressing Start (status = 'active') ─────────────
function pollStatus() {
  fetch('checkexamstatus.php')
    .then(r => r.json())
    .then(d => {
      if (d.status === 'active') window.location.href = "start_exam.php";
    })
    .catch(() => {});
}
setInterval(pollStatus, 10000);
</script>

</body>
</html>