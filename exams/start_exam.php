<?php
session_start();
include("../db.php");

if (!isset($_SESSION['exam_id'])) {
    header("Location: join_exam.php");
    exit();
}

$exam_id = $_SESSION['exam_id'];

// ---------------- GET EXAM ----------------
$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    die("Invalid exam.");
}

// ---------------- GET QUESTIONS + OPTIONS ----------------
$qstmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY question_id ASC");
$qstmt->bind_param("i", $exam_id);
$qstmt->execute();
$qresult = $qstmt->get_result();

$questions = [];
while ($q = $qresult->fetch_assoc()) {
    $qid = $q['question_id'];

    $opt = $conn->prepare("SELECT * FROM options WHERE question_id = ? ORDER BY option_id ASC");
    $opt->bind_param("i", $qid);
    $opt->execute();
    $opts = $opt->get_result();

    $options = [];
    while ($o = $opts->fetch_assoc()) {
        $options[] = $o;
    }

    $q['options'] = $options;
    $questions[]  = $q;
}

// Guard: if no questions exist, show a friendly message
if (empty($questions)) {
    die("No questions have been added to this exam yet.");
}

// Per-question time (fallback to 30 s if duration_minutes is 0)
$totalSeconds = max(($exam['duration_minutes'] ?? 0) * 60, count($questions) * 30);
$timePerQ     = intval($totalSeconds / count($questions));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($exam['title']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#f4f6fb;
  --card:#ffffff;
  --accent:#7c3aed;
  --accent2:#a855f7;
  --text:#0f172a;
  --muted:#64748b;
  --radius:16px;
}
body{
  font-family:'Nunito',sans-serif;
  background:var(--bg);
  color:var(--text);
  min-height:100vh;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:flex-start;
  padding:20px;
  overflow-x:hidden;
  position:relative;
}
body::before,body::after{
  content:'';position:fixed;border-radius:50%;
  filter:blur(80px);opacity:.15;pointer-events:none;
  animation:drift 8s ease-in-out infinite alternate;
}
body::before{width:400px;height:400px;background:#c4b5fd;top:-120px;left:-100px}
body::after{width:350px;height:350px;background:#67e8f9;bottom:-100px;right:-80px;animation-delay:-4s}
@keyframes drift{to{transform:translate(40px,30px)}}

/* ── Top bar ── */
.topbar{
  display:flex;align-items:center;justify-content:space-between;
  width:100%;max-width:760px;margin-bottom:20px;position:relative;z-index:1;
}
.logo{font-size:24px;font-weight:900;
  background:linear-gradient(135deg,#a855f7,#06b6d4);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

.q-counter{
  font-size:13px;font-weight:700;color:var(--muted);
}
.q-counter span{color:var(--text)}

/* ── Timer ── */
.timer-wrap{
  display:flex;align-items:center;gap:8px;
 background:#ffffff;
  border:1px solid #e2e8f0;
  border-radius:30px;padding:6px 14px;
  border-radius:30px;padding:8px 16px;
}
.timer-icon{font-size:16px}
.timer-val{font-size:18px;font-weight:900;min-width:44px;text-align:center}
.timer-val.warning{color:#f87171}

/* ── Progress bar ── */
.progress-bar{
  width:100%;max-width:760px;height:6px;
  background:#e2e8f0;border-radius:99px;
  margin-bottom:24px;overflow:hidden;position:relative;z-index:1;
}
.progress-fill{
  height:100%;border-radius:99px;
  background:linear-gradient(90deg,var(--accent),var(--accent2));
  transition:width .4s ease;
}

/* ── Card ── */
.card{
  background:var(--card);
  border:1px solid rgba(255,255,255,.08);
  border-radius:var(--radius);
  padding:36px 32px;
  width:100%;max-width:760px;
  box-shadow:0 32px 80px rgba(0,0,0,.5);
  position:relative;z-index:1;
}

/* ── Question ── */
.q-label{
  font-size:11px;font-weight:700;text-transform:uppercase;
  letter-spacing:1px;color:#a855f7;margin-bottom:12px;
}
.q-text{
  font-size:22px;font-weight:900;line-height:1.4;
  margin-bottom:32px;
}

/* ── Options grid ── */
.options{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:14px;
  margin-bottom:32px;
}
@media(max-width:540px){.options{grid-template-columns:1fr}}

/* Colour accents matching the join-page icon boxes */
.option{
  padding:18px 20px;border-radius:14px;
  border:2px solid transparent;
  cursor:pointer;font-size:16px;font-weight:700;
  font-family:'Nunito',sans-serif;
  display:flex;align-items:center;gap:12px;
  transition:transform .15s,box-shadow .15s,border-color .15s;
  text-align:left;
  background:#f8fafc;
  color:var(--text);
}
.option:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.3)}
.option:active{transform:translateY(0)}

/* Each of the four shapes/colours */
.option:nth-child(1){border-color:rgba(239,68,68,.4)}
.option:nth-child(1) .shape{background:#ef4444}
.option:nth-child(2){border-color:rgba(59,130,246,.4)}
.option:nth-child(2) .shape{background:#3b82f6}
.option:nth-child(3){border-color:rgba(34,197,94,.4)}
.option:nth-child(3) .shape{background:#22c55e}
.option:nth-child(4){border-color:rgba(250,204,21,.4)}
.option:nth-child(4) .shape{background:#facc15;color:#111}

.shape{
  width:36px;height:36px;min-width:36px;border-radius:8px;
  display:flex;align-items:center;justify-content:center;
  font-size:18px;font-weight:900;
}

/* Selected state */
.option.selected{border-color:#a855f7 !important;background:rgba(124,58,237,.25) !important;
  box-shadow:0 0 0 3px rgba(168,85,247,.25)}
.option.selected .shape{outline:3px solid #a855f7;outline-offset:2px}

/* ── Next button ── */
.btn-next{
  width:100%;padding:16px;
  background:linear-gradient(135deg,var(--accent),var(--accent2));
  border:none;border-radius:12px;color:#fff;
  font-family:'Nunito',sans-serif;font-size:18px;font-weight:900;
  cursor:pointer;transition:transform .15s,box-shadow .15s;
  box-shadow:0 8px 24px rgba(124,58,237,.4);
}
.btn-next:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(124,58,237,.5)}
.btn-next:active{transform:translateY(0)}
.btn-next:disabled{opacity:.5;cursor:not-allowed;transform:none}

/* ── Feedback flash ── */
.feedback{
  display:none;position:fixed;inset:0;z-index:99;
  align-items:center;justify-content:center;
  font-size:64px;animation:pop .4s ease;
}
.feedback.show{display:flex}
@keyframes pop{from{transform:scale(.5);opacity:0}to{transform:scale(1);opacity:1}}
</style>
</head>
<body>

<!-- Top bar -->
<div class="topbar">
  <div class="q-counter">Question <span id="qNum">1</span> of <span><?= count($questions) ?></span></div>
  <div class="timer-wrap">
    <span class="timer-icon">⏱</span>
    <span class="timer-val" id="timerVal">??</span>
  </div>
</div>

<!-- Progress -->
<div class="progress-bar">
  <div class="progress-fill" id="progressFill" style="width:0%"></div>
</div>

<!-- Question card -->
<div class="card">
  <div class="q-label" id="qLabel">Question 1</div>
  <div class="q-text"  id="qText"></div>
  <div class="options" id="optionsGrid"></div>
  <button class="btn-next" id="nextBtn" onclick="nextQuestion()">Next →</button>
</div>

<!-- Brief feedback flash -->
<div class="feedback" id="feedback"></div>

<!-- Hidden form for submission -->
<form method="POST" id="submitForm" action="submit_exam.php"></form>

<script>
// ── Data from PHP ────────────────────────────────────────────────
const questions    = <?= json_encode($questions) ?>;
const timePerQ     = <?= $timePerQ ?>;
const SHAPES       = ['▲','◆','●','■'];

let current  = 0;
let answers  = {};   // { question_id: option_id }
let timerInt = null;
let timeLeft = timePerQ;

// ── Render question ──────────────────────────────────────────────
function loadQuestion() {
  const q = questions[current];

  // Header
  document.getElementById('qNum').textContent   = current + 1;
  document.getElementById('qLabel').textContent = `Question ${current + 1}`;
  document.getElementById('qText').textContent  = q.question_text;

  // Progress
  const pct = (current / questions.length) * 100;
  document.getElementById('progressFill').style.width = pct + '%';

  // Options — use index-based IDs to avoid XSS via inline onclick
  const grid = document.getElementById('optionsGrid');
  grid.innerHTML = '';

  q.options.forEach((opt, i) => {
    const btn = document.createElement('button');
    btn.className = 'option';
    btn.type      = 'button';

    const shape = document.createElement('span');
    shape.className   = 'shape';
    shape.textContent = SHAPES[i] || '?';

    const text = document.createElement('span');
    text.textContent = opt.option_text;

    btn.appendChild(shape);
    btn.appendChild(text);

    // Restore previous selection if user navigated back (future-proof)
    if (answers[q.question_id] === opt.option_id) {
      btn.classList.add('selected');
    }

    btn.addEventListener('click', () => selectOption(btn, q.question_id, opt.option_id));
    grid.appendChild(btn);
  });

  // Button label
  const nextBtn = document.getElementById('nextBtn');
  nextBtn.textContent = current === questions.length - 1 ? 'Submit ✓' : 'Next →';

  resetTimer();
}

// ── Select option ────────────────────────────────────────────────
function selectOption(el, qid, optId) {
  document.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  answers[qid] = optId;
}

// ── Next / Submit ────────────────────────────────────────────────
function nextQuestion() {
  // Append hidden input for this question's answer
  const q    = questions[current];
  const inp  = document.createElement('input');
  inp.type   = 'hidden';
  inp.name   = 'q' + q.question_id;
  inp.value  = answers[q.question_id] ?? '';
  document.getElementById('submitForm').appendChild(inp);

  current++;

  if (current >= questions.length) {
    clearInterval(timerInt);
    document.getElementById('submitForm').submit();
    return;
  }

  loadQuestion();
}

// ── Timer ────────────────────────────────────────────────────────
function resetTimer() {
  clearInterval(timerInt);
  timeLeft = timePerQ;
  tick();
  timerInt = setInterval(() => {
    timeLeft--;
    if (timeLeft <= 0) {
      clearInterval(timerInt);
      nextQuestion();
    } else {
      tick();
    }
  }, 1000);
}

function tick() {
  const el = document.getElementById('timerVal');
  const m  = Math.floor(timeLeft / 60);
  const s  = timeLeft % 60;
  el.textContent = m > 0
    ? `${m}:${String(s).padStart(2,'0')}`
    : String(timeLeft);
  el.classList.toggle('warning', timeLeft <= 5);
}

// ── Kick off ─────────────────────────────────────────────────────
loadQuestion();
</script>

</body>
</html>