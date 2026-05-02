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
    $conn->close();
    die("No questions have been added to this exam yet.");
}

// Close connection - data already fetched into PHP arrays
if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }

// Per-question time - use 'duration' column (it's in minutes)
$totalSeconds = max(($exam['duration'] ?? 0) * 60, count($questions) * 30);
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
  --accent:#7c3aed;
  --accent2:#a855f7;
  --text:#f1f5f9;
  --muted:#94a3b8;
  --radius:20px;
}
body{
  font-family:'Nunito',sans-serif;
  background:linear-gradient(135deg,#0d0d2b,#1e1b4b);
  color:var(--text);
  min-height:100vh;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:flex-start;
  padding:24px 20px 60px;
  overflow-x:hidden;
  position:relative;
}
body::before,body::after{
  content:'';position:fixed;border-radius:50%;
  filter:blur(110px);opacity:.22;pointer-events:none;
  animation:drift 8s ease-in-out infinite alternate;
}
body::before{width:500px;height:500px;background:#7c3aed;top:-150px;left:-150px}
body::after{width:420px;height:420px;background:#06b6d4;bottom:-150px;right:-150px;animation-delay:-4s}
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
  display:flex;align-items:center;gap:10px;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(168,85,247,.3);
  backdrop-filter:blur(20px);
  border-radius:30px;padding:8px 18px;
  color:#f1f5f9;
}
.timer-icon{font-size:16px}
.timer-val{font-size:18px;font-weight:900;min-width:44px;text-align:center}
.timer-val.warning{color:#f87171;animation:pulseWarn 1s ease-in-out infinite}
@keyframes pulseWarn{0%,100%{opacity:1}50%{opacity:.5}}

/* ── Progress bar ── */
.progress-bar{
  width:100%;max-width:760px;height:8px;
  background:rgba(255,255,255,.08);border-radius:99px;
  margin-bottom:24px;overflow:hidden;position:relative;z-index:1;
}
.progress-fill{
  height:100%;border-radius:99px;
  background:linear-gradient(90deg,#facc15,#f97316,#a855f7);
  transition:width .4s ease;
  box-shadow:0 0 16px rgba(168,85,247,.5);
}

/* ── Card ── */
.card{
  background:rgba(255,255,255,.05);
  border:1px solid rgba(168,85,247,.3);
  backdrop-filter:blur(20px);
  -webkit-backdrop-filter:blur(20px);
  border-radius:var(--radius);
  padding:40px 36px;
  width:100%;max-width:760px;
  box-shadow:0 25px 80px rgba(0,0,0,.5);
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
  transition:transform .15s,box-shadow .15s,border-color .15s,background .15s;
  text-align:left;
  background:rgba(255,255,255,.04);
  color:var(--text);
  backdrop-filter:blur(10px);
}
.option:hover{
  transform:translateY(-2px);
  box-shadow:0 12px 28px rgba(0,0,0,.35);
  background:rgba(255,255,255,.07);
}
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
<form method="POST" id="submitForm" action="submit_exam.php">
  <input type="hidden" name="eid" value="<?= (int)$exam_id ?>">
  <input type="hidden" name="pid" value="<?= (int)($_SESSION['player_id'] ?? 0) ?>">
</form>

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

  // Render based on question type
  const grid = document.getElementById('optionsGrid');
  grid.innerHTML = '';
  if (q.question_type === 'practical') {
    // PRACTICAL - show PDF + text answer
    grid.style.gridTemplateColumns = '1fr'; // full width

    const pdfUrl = q.options.length > 0 ? q.options[0].option_text : '';

    if (pdfUrl) {
        const pdfWrap = document.createElement('div');
        pdfWrap.style.cssText = 'width:100%; margin-bottom:16px;';
        pdfWrap.innerHTML = `
            <div style="margin-bottom:10px;">
                <a href="${pdfUrl}" target="_blank" 
                   style="display:inline-block; padding:8px 14px; background:#667eea; 
                          color:white; text-decoration:none; border-radius:6px; font-weight:600;">
                    📄 Open PDF in new tab
                </a>
            </div>
            <iframe 
               src="<?= APP_BASE_URL ?>/pdfjs/web/viewer.html?file=${encodeURIComponent('<?= APP_BASE_URL ?>/exams/exam_pdf_proxy.php?url=' + encodeURIComponent(pdfUrl))}"
               style="width:100%; height:500px; border:2px solid #e2e8f0; border-radius:8px;"
               allowfullscreen>
            </iframe>
        `;
        grid.appendChild(pdfWrap);
    }

    // Project link submission
    const linkBox = document.createElement('div');
    linkBox.style.cssText = 'background:rgba(124,58,237,.12); border:2px solid rgba(124,58,237,.4); border-radius:12px; padding:20px; margin-top:8px;';
    linkBox.innerHTML = `
        <p style="font-weight:800; font-size:15px; color:#e2d9f3; margin-bottom:6px;">
            📎 Submit your project link
        </p>
        <p style="font-size:13px; color:#a89cc8; margin-bottom:14px;">
            Paste the link to your completed project (GitHub, Google Drive, Tinkercad, etc.)
        </p>
        <input
            type="url"
            id="projectLink_${q.question_id}"
            placeholder="https://github.com/yourname/project  or  https://drive.google.com/..."
            style="width:100%; padding:14px 16px; border-radius:8px; border:2px solid #4c3a8f;
                   background:#12103a; color:#f1f5f9; font-size:14px; font-family:inherit;
                   outline:none; transition:border-color .2s;"
            value="${answers[q.question_id] ?? ''}"
        />
        <p style="font-size:12px; color:#7c6aab; margin-top:10px;">
            ℹ️ Make sure your link is publicly accessible before submitting.
        </p>
    `;

    const linkInput = linkBox.querySelector('input');
    linkInput.addEventListener('input', (e) => {
        answers[q.question_id] = e.target.value;
    });
    linkInput.addEventListener('focus', (e) => { e.target.style.borderColor = '#a855f7'; });
    linkInput.addEventListener('blur',  (e) => { e.target.style.borderColor = '#4c3a8f'; });

    grid.appendChild(linkBox);

} else if (q.question_type === 'true_false') {
    // TRUE/FALSE question - render as 2 buttons
    ['True', 'False'].forEach((text, i) => {
      const btn = document.createElement('button');
      btn.className = 'option';
      btn.type      = 'button';

      const shape = document.createElement('span');
      shape.className   = 'shape';
      shape.textContent = SHAPES[i] || '?';

      const label = document.createElement('span');
      label.textContent = text;

      btn.appendChild(shape);
      btn.appendChild(label);

      // Restore previous selection if user navigated back
      if (answers[q.question_id] === q.options[i].option_id) {
        btn.classList.add('selected');
      }

      btn.addEventListener('click', () => selectOption(btn, q.question_id, q.options[i].option_id));
      grid.appendChild(btn);
    });

  } else if (q.question_type === 'essay') {
    // ESSAY question - render text area
    const textarea = document.createElement('textarea');
    textarea.id = 'essayAnswer_' + q.question_id;
    textarea.placeholder = 'Type your answer here...';
    textarea.style.cssText = `
      width:100%; min-height:170px; padding:16px;
      border-radius:12px; border:2px solid rgba(168,85,247,.25);
      background:rgba(15,15,40,.55); color:#f1f5f9;
      font-size:15px; font-family:inherit; font-weight:600;
      outline:none; transition:border-color .2s, box-shadow .2s;
      resize:vertical;
    `;
    textarea.addEventListener('focus', e => {
      e.target.style.borderColor = '#a855f7';
      e.target.style.boxShadow = '0 0 0 4px rgba(168,85,247,.18)';
    });
    textarea.addEventListener('blur', e => {
      e.target.style.borderColor = 'rgba(168,85,247,.25)';
      e.target.style.boxShadow = 'none';
    });
    textarea.value = answers[q.question_id] ?? '';

    textarea.addEventListener('input', (e) => {
      answers[q.question_id] = e.target.value;
    });

    grid.appendChild(textarea);

  } else {
    // MCQ (multiple choice) - render as buttons
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

      // Restore previous selection if user navigated back
      if (answers[q.question_id] === opt.option_id) {
        btn.classList.add('selected');
      }

      btn.addEventListener('click', () => selectOption(btn, q.question_id, opt.option_id));
      grid.appendChild(btn);
    });
  }

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