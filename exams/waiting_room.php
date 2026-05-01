<?php
session_start();
include('../db.php');

if (!isset($_SESSION['exam_id'])) {
    header("Location: index.php");
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
    $session_id_val = $_SESSION['session_id'] ?? null;
$stmt = $conn->prepare("
    INSERT INTO players 
    (nickname, mode, school, group_nbr, exam_id, grade, stream, session_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
"ssssissi",
$m,
$mode,
$school,
$group_nbr,
$exam_id,
$grade,
$stream,
$session_id_val
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
  width:100%;max-width:520px;
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
  font-size:26px;font-weight:900;margin-bottom:6px;text-align:center;
  background:linear-gradient(135deg,#facc15,#f97316);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.player-name{color:var(--muted);font-size:14px;margin-bottom:24px;text-align:center}
.player-name span{color:#a855f7;font-weight:800}

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

.field{margin-bottom:18px}
label,label.lbl{
  display:block;font-size:11px;font-weight:700;letter-spacing:1.5px;
  text-transform:uppercase;color:#cbd5e1;margin-bottom:8px;
}
input[type=text],textarea{
  width:100%;
  background:rgba(15,15,40,.6);
  border:1.5px solid rgba(168,85,247,.25);
  border-radius:12px;
  padding:14px 18px;
  font-size:15px;
  font-family:'Nunito',sans-serif;
  font-weight:600;
  color:var(--text);
  outline:none;
  transition:border-color .2s,background .2s,box-shadow .2s;
}

input[type=text]:focus,textarea:focus{
  border-color:var(--accent2);
  background:rgba(15,15,40,.85);
  box-shadow:0 0 0 4px rgba(168,85,247,.18);
}
input[type=text]::placeholder,textarea::placeholder{color:#64748b;font-weight:400}

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
  background:rgba(34,197,94,.12);
  border:1px solid rgba(34,197,94,.4);
  border-radius:10px;
  padding:12px 16px;
  font-size:14px;
  color:#86efac;
  margin-bottom:18px;
  text-align:center;
  font-weight:700;
}
.players-online{
  display:flex;align-items:center;justify-content:center;gap:8px;
  color:var(--muted);font-size:13px;margin-top:16px;
}
.players-online strong{color:var(--text)}
select{
  width:100%;
  padding:14px 16px;
  border-radius:12px;
  border:1.5px solid rgba(168,85,247,.25);
  background:rgba(15,15,40,.6);
  color:var(--text);
  font-family:'Nunito',sans-serif;
  font-weight:700;
  font-size:15px;
  outline:none;
  transition:border-color .2s,box-shadow .2s;
  appearance:none;
  -webkit-appearance:none;
  background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'><path fill='%23a855f7' d='M6 8L0 0h12z'/></svg>");
  background-repeat:no-repeat;
  background-position:right 18px center;
  padding-right:42px;
}
select:focus{
  border-color:var(--accent2);
  box-shadow:0 0 0 4px rgba(168,85,247,.18);
}
select option{background:#1e1b4b;color:var(--text)}
</style>
</head>
<body>
  <?php $back_to = 'join_exam.php'; $back_label = 'Join Exam'; include('nav_back.php'); ?>


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
<?php $conn->close(); ?>
