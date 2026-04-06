<?php
session_start();
include("../db.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname      = trim($_POST['nickname']      ?? '');

    if (!$nickname) {
        $error = "Please enter a nickname.";
    } else {
        $stmt = $conn->prepare("UPDATE players SET nickname = ? WHERE player_id = ?");
        $stmt->bind_param("si", $nickname, $_SESSION['player_id']);
        $stmt->execute();
        $_SESSION['nickname'] = $nickname;
        header("Location: waiting.php");
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
  overflow:hidden;
  position:relative
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
  background:#f1f5f9;
  border:1.5px solid rgba(0,0,0,.1);
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
  background:#fff
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
</style>
</head>
<body>
<div class="logo">QuizBlast</div>
<p class="tagline">Join the fun — enter your game PIN</p>
<div class="icons">
  <div class="icon-box">▲</div>
  <div class="icon-box">◆</div>
  <div class="icon-box">●</div>
  <div class="icon-box">■</div>
</div>
<div class="card">
  <h2>Enter Game</h2>
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    
    <div class="field">
      <label>Your Nickname</label>
      <input type="text" name="nickname" placeholder="e.g. StarPlayer"
             value="<?= htmlspecialchars($_POST['nickname'] ?? '') ?>" maxlength="30" autocomplete="off">
    </div> 
    <button type="submit" class="btn">JOIN GAME &rarr;</button>
  </form>
</div>
</body>
</html>