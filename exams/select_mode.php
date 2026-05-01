<?php
session_start();
include("../db.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode    = trim($_POST['mode']      ?? 'individual');
    $grade = trim($_POST['grade'] ?? '');
    $stream = trim($_POST['stream'] ?? '');
    if (!$mode || !$grade || !$stream) {
        $error = "Please select all options.";
    } else {
        
       
            //nickname can be optional 
            $upd = $conn->prepare("UPDATE players SET mode = ? WHERE player_id = ?");
$upd->bind_param("si", $mode, $_SESSION['player_id']);
$upd->execute();

$_SESSION['mode'] = $mode;

if ($mode === 'group') {
    $_SESSION['nickname'] = null;
    $_SESSION['grade'] = $grade;
    $_SESSION['stream'] = $stream;

    header("Location: waiting_room.php");
    exit();
} else {
    $stmt = $conn->prepare("UPDATE players SET grade=?, stream=? WHERE player_id=?");
    $stmt->bind_param("ssi", $grade, $stream, $_SESSION['player_id']);
    $stmt->execute();

    header("Location: add_name.php");
    exit();
}
            exit();
        
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Join Game</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

/* LIGHT THEME COLORS */
:root{
  --bg:linear-gradient(135deg,#0d0d2b,#1e1b4b);
  --card:rgba(255,255,255,.05);
  --accent:#7c3aed;
  --accent2:#a855f7;
  --text:#f1f5f9;
  --muted:#94a3b8;
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
  overflow:hidden;
  position:relative
}
/* MODE SELECT CARDS */
.mode-select{
  display:flex;
  gap:12px;
}

.mode-card{
  flex:1;
  cursor:pointer;
}

.mode-card input{
  display:none;
}

.mode-content{
  padding:16px;
  border-radius:12px;
  text-align:center;
  font-weight:800;
  background:rgba(255,255,255,.06);
  border:2px solid rgba(168,85,247,.2);
  color:#f1f5f9;
  transition:all .2s;
}

/* hover */
.mode-content:hover{
  transform:translateY(-2px);
}

/* selected */
.mode-card input:checked + .mode-content{
  background:rgba(124,58,237,.1);
  border-color:var(--accent);
  color:var(--accent);
}

/* softer background glow */
body::before,body::after{
  content:'';
  position:fixed;
  border-radius:50%;
  filter:blur(80px);
  opacity:.12;
  pointer-events:none;
  animation:drift 8s ease-in-out infinite alternate
}

body::before{
  width:400px;height:400px;
  background:var(--accent);
  top:-120px;left:-100px
}

body::after{
  width:350px;height:350px;
  background:#06b6d4;
  bottom:-100px;right:-80px;
  animation-delay:-4s
}

@keyframes drift{
  to{transform:translate(40px,30px)}
}

.logo{
  font-size:44px;
  font-weight:900;
  letter-spacing:-1px;
  margin-bottom:6px;
  background:linear-gradient(135deg,#a855f7,#06b6d4);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  background-clip:text
}

.tagline{
  color:var(--muted);
  font-size:14px;
  margin-bottom:36px
}

.icons{
  display:flex;
  gap:10px;
  justify-content:center;
  margin-bottom:28px
}

.icon-box{
  width:48px;height:48px;
  border-radius:10px;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:22px;
  color:#fff
}

.icon-box:nth-child(1){background:#ef4444}
.icon-box:nth-child(2){background:#3b82f6}
.icon-box:nth-child(3){background:#22c55e}
.icon-box:nth-child(4){background:#facc15}

.card{
  background:var(--card);
  border:1px solid rgba(0,0,0,.06);
  border-radius:var(--radius);
  padding:40px 36px;
  width:100%;
  max-width:420px;
  box-shadow:0 20px 50px rgba(0,0,0,.08);
  position:relative;
  z-index:1
}

.card h2{
  font-size:22px;
  font-weight:900;
  margin-bottom:28px;
  text-align:center
}

.field{margin-bottom:20px}

label{
  display:block;
  font-size:11px;
  font-weight:700;
  letter-spacing:1px;
  text-transform:uppercase;
  color:var(--muted);
  margin-bottom:8px
}

/* FIXED INPUTS */
input{
  width:100%;
  background:rgba(15,15,40,.6);
  border:1.5px solid rgba(168,85,247,.25);
  border-radius:12px;
  padding:14px 18px;
  font-size:18px;
  font-family:'Nunito',sans-serif;
  font-weight:700;
  color:var(--text);
  outline:none;
  transition:border-color .2s,background .2s
}

input[type=number]{letter-spacing:4px}

input:focus{
  border-color:var(--accent);
  background:rgba(15,15,40,.85);
  color:#f1f5f9
}

input::placeholder{
  color:var(--muted);
  font-weight:400;
  letter-spacing:0
}

.btn{
  width:100%;
  padding:16px;
  background:linear-gradient(135deg,var(--accent),var(--accent2));
  border:none;
  border-radius:12px;
  color:#fff;
  font-family:'Nunito',sans-serif;
  font-size:18px;
  font-weight:900;
  cursor:pointer;
  transition:transform .15s,box-shadow .15s;
  box-shadow:0 8px 24px rgba(124,58,237,.3);
  margin-top:8px
}

.btn:hover{
  transform:translateY(-2px);
  box-shadow:0 12px 32px rgba(124,58,237,.4)
}

.btn:active{transform:translateY(0)}
.checkbox-group{
  display:flex;
  flex-direction:row;
  justify-content:flex-start;
  gap:8px;
  
}
.error{
  background:rgba(239,68,68,.1);
  border:1px solid rgba(239,68,68,.3);
  border-radius:10px;
  padding:12px 16px;
  font-size:14px;
  color:#dc2626;
  margin-bottom:20px;
  text-align:center
}
select{
  width:100%;
  padding:12px;
  border-radius:10px;
  border:1px solid rgba(168,85,247,.25);
  background:rgba(15,15,40,.6);
  color:#f1f5f9;
  font-weight:700;
}

textarea{
  width:100%;
  padding:12px;
  border-radius:10px;
  border:1px solid rgba(168,85,247,.25);
  background:rgba(15,15,40,.6);
  color:#f1f5f9;
}
</style>
    <link rel="stylesheet" href="/Exam-mis/exams/assets/exam-theme.css">
</head>
<body class="exam-dark">
<div class="icons">
  <div class="icon-box">▲</div>
  <div class="icon-box">◆</div>
  <div class="icon-box">●</div>
  <div class="icon-box">■</div>
</div>
<div class="card">
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="field">
         <!-- MODE -->
    <div class="field">
      <label>Playing As</label>

      <div class="mode-select">
        <label class="mode-card">
          <input type="radio" name="mode" value="group" required>
          <div class="mode-content">
            👥 Group
          </div>
        </label>

        <label class="mode-card">
          <input type="radio" name="mode" value="individual">
          <div class="mode-content">
            🧍 Individual
          </div>
        </label>
      </div>
    </div>
    <div class="field">
      <label class="block mb-2 font-semibold">Grade</label>
       <select name="grade" class="w-full p-2 border rounded mb-4" required>
          <option value="">Select Grade</option>
          <option value="Nursery 1">Nursery 1</option>
          <option value="Nursery 2">Nursery 2</option>
          <option value="Nursery 3">Nursery 3</option>
          <option value="Grade 1">Grade 1</option>
          <option value="Grade 2">Grade 2</option>
          <option value="Grade 3">Grade 3</option>
          <option value="Grade 4">Grade 4</option>
          <option value="Grade 5">Grade 5</option>
          <option value="Grade 6">Grade 6</option>
          <option value="Grade 7">Grade 7</option>
          <option value="Grade 8">Grade 8</option>
          <option value="Grade 9">Grade 9</option>
          <option value="Grade 10">Grade 10</option>
          <option value="Grade 11">Grade 11</option>
          <option value="Grade 12">Grade 12</option>
      </Select>
    </div>
    <div class="field">
      <label class="block mb-2 font-semibold">Stream</label>
       <select name="stream" class="w-full p-2 border rounded mb-4" required>
          <option value="">Select Stream</option>
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
      </Select>
    </div>

    <button type="submit" class="btn">JOIN GAME</button>
  </div>
  </form>
</div>
</body>
</html>
<?php $conn->close(); ?>
