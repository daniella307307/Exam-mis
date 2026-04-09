<?php
session_start();
include('../db.php');

if (!isset($_SESSION['exam_id'])) {
    header("Location: ../../index.php");
    exit();
}


$exam_id   = $_SESSION['exam_id'];
$player_id = $_SESSION['player_id'] ?? null;
$grade = $_SESSION['grade'] ?? null;
$stream = $_SESSION['stream'] ?? null;
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
    $membersArray = $_POST['members'] ?? [];
    $membersArray = array_map('trim', $membersArray); // clean each name
    $membersArray = array_filter($membersArray); // remove empty ones

$members = implode(", ", $membersArray); // convert to string
    // Make sure your players table has: mode VARCHAR(20), school VARCHAR(255),
    // grade VARCHAR(100), group_nbr int
    //generate a unique group number
    $group_nbr = mt_rand(1000,9999);
   //save each group memeber as a separate player with the same group number
   foreach($membersArray as $m){
    $stmt = $conn->prepare("
        INSERT INTO players 
        (nickname, mode, school, group_nbr, exam_id, grade, stream) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
    "ssssiss",
    $m,
    $mode,
    $school,
    $group_nbr,
    $exam_id,
    $grade,
    $stream
);

    $stmt->execute();
}
    $groupSaved = true;
    if($groupSaved){
      header("Location: waiting.php");
    }
}

$start_time = strtotime($exam['start_time']);
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
  --bg:#f8fafc;
  --card:#ffffff;
  --accent:#7c3aed;
  --accent2:#a855f7;
  --text:#0f172a;
  --muted:#64748b;
  --radius:16px
}
body{
  font-family:'Nunito',sans-serif;
  background:var(--bg);
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
  filter:blur(80px);opacity:.12;pointer-events:none;
  animation:drift 8s ease-in-out infinite alternate;
}
body::before{width:400px;height:400px;background:var(--accent);top:-120px;left:-100px}
body::after{width:350px;height:350px;background:#06b6d4;bottom:-100px;right:-80px;animation-delay:-4s}
@keyframes drift{to{transform:translate(40px,30px)}}

.logo{font-size:36px;font-weight:900;letter-spacing:-1px;margin-bottom:4px;
  background:linear-gradient(135deg,#a855f7,#06b6d4);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.tagline{color:var(--muted);font-size:13px;margin-bottom:28px}

.card{
  background:var(--card);
  border:1px solid rgba(0,0,0,.06);
  border-radius:var(--radius);
  padding:36px 32px;
  width:100%;max-width:480px;
  box-shadow:0 20px 50px rgba(0,0,0,.08);
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

.exam-title{font-size:20px;font-weight:900;margin-bottom:6px;text-align:center}
.player-name{color:var(--muted);font-size:13px;margin-bottom:24px;text-align:center}
.player-name span{color:#a855f7;font-weight:700}

.countdown-wrap{
  background:#f1f5f9;
  border:1px solid rgba(0,0,0,.06);
  border-radius:12px;
  padding:20px;
  text-align:center;
  margin-bottom:24px;
}
.countdown-label{font-size:11px;font-weight:700;text-transform:uppercase;
  letter-spacing:1px;color:var(--muted);margin-bottom:8px}
.countdown{font-size:48px;font-weight:900;letter-spacing:4px;
  background:linear-gradient(135deg,#a855f7,#06b6d4);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

.divider{
  height:1px;
  background:rgba(0,0,0,.06);
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
  background:#f1f5f9;
  border:1.5px solid rgba(0,0,0,.1);
  border-radius:12px;
  padding:12px 16px;
  font-size:15px;
  font-family:'Nunito',sans-serif;
  font-weight:600;
  color:var(--text);
  outline:none;
  transition:border-color .2s,background .2s;
}

input[type=text]:focus,textarea:focus{
  border-color:var(--accent);
  background:#fff;
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
  display:flex;align-items:center;justify-content:center;gap:8px;
  color:var(--muted);font-size:13px;margin-top:16px;
}
.players-online strong{color:var(--text)}
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
 
  <div class="divider"></div>

  <?php if ($groupSaved): ?>
    <div class="success">✓ Your details have been saved!</div>
  <?php endif; ?>

 <form method="POST">

  <div class="field">
    <label class="lbl">Number of Group Members</label>
    <select id="memberCount" onchange="generateFields()">
      <option value="">Select</option>
      <option value="2">2</option>
      <option value="3">3</option>
      <option value="4">4</option>
      <option value="5">5</option>
      <option value="6">6</option>
      <option value="7">7</option>
      <option value="8">8</option>
    </select>
  </div>

  <!-- Dynamic fields -->
  <div id="membersContainer"></div>

  <button type="submit" name="save_group" class="btn-save">
    Add Members & Save
  </button>

</form>


</div>
<script id="js-members">
function generateFields() {
  const count = document.getElementById("memberCount").value;
  const container = document.getElementById("membersContainer");

  container.innerHTML = "";

  for (let i = 1; i <= count; i++) {
    container.innerHTML += `
      <div class="field">
        <label>Member ${i}</label>
        <input type="text" name="members[]" placeholder="Enter name" required>
      </div>
    `;
  }
}
</script>

<script>
// ── Mode toggle ──────────────────────────────────────────────────
function setMode(mode) {
  document.getElementById('modeInput').value = mode;
  document.getElementById('groupFields').classList.toggle('visible', mode === 'group');
  document.getElementById('btnInd').classList.toggle('active', mode === 'individual');
  document.getElementById('btnGrp').classList.toggle('active', mode === 'group');
}



</script>

</body>
</html>