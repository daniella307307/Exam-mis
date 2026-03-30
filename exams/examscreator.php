<?php
include('../db.php');
// getting exam details 
$exam_title = $_POST['exam_title'] ?? '';
$exam_topic = $_POST['exam_topic'] ?? '';
$exam_grade = $_POST['exam_grade'] ?? '';
$nbr_questions = $_POST['num_questions'] ?? 10;
$time_limit = $_POST['time_limit'] ?? 20;
$mode= $_POST['mode'] ?? 'ai';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Quizly — AI Exam Creator</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet" />
<style>
  :root{
  --bg: #f5f6fa;
  --surface: #ffffff;
  --surface2: #f0f2f5;
  --border: #e1e4eb;
  --accent: #5b6ef5;
  --accent2: #f5a623;
  --accent3: #22d3a5;
  --text: #0c0e14;
  --muted: #6b7194;
  --danger: #f54b5e;
  --radius: 14px;
}

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    overflow-x: hidden;
  }

  /* GRID BACKGROUND */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(91,110,245,.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(91,110,245,.04) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none;
    z-index: 0;
  }

  .wrap {
    position: relative;
    z-index: 1;
    max-width: 860px;
    margin: 0 auto;
    padding: 40px 24px 80px;
  }

  /* HEADER */
  header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 48px;
  }
  .logo-icon {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, var(--accent), var(--accent3));
    border-radius: 12px;
    display: grid;
    place-items: center;
    font-size: 22px;
  }
  .logo-text {
    font-family: 'Syne', sans-serif;
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.5px;
  }
  .logo-text span { color: var(--accent); }
  .badge {
    margin-left: auto;
    background: rgba(213, 217, 242, 0.15);
    border: 1px solid rgba(213, 217, 242, 0.15);
    color: var(--accent);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: .8px;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 100px;
  }

  /* STEPS */
  .steps {
    display: flex;
    gap: 0;
    margin-bottom: 36px;
    position: relative;
  }
  .steps::after {
    content: '';
    position: absolute;
    top: 18px; left: 18px; right: 18px;
    height: 2px;
    background: var(--border);
    z-index: 0;
  }
  .step {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    position: relative;
    z-index: 1;
  }
  .step-dot {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: var(--surface2);
    border: 2px solid var(--border);
    display: grid;
    place-items: center;
    font-family: 'Syne', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: var(--muted);
    transition: all .3s;
  }
  .step.active .step-dot {
    background: var(--accent);
    border-color: var(--accent);
    color: #fff;
    box-shadow: 0 0 0 6px rgba(253, 253, 255, 0.2);
  }
  .step.done .step-dot {
    background: var(--accent3);
    border-color: var(--accent3);
    color: #fff;
  }
  .step-label {
    font-size: 11px;
    color: var(--muted);
    letter-spacing: .4px;
    white-space: nowrap;
  }
  .step.active .step-label { color: var(--text); }

  /* CARD */
  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 32px;
    margin-bottom: 20px;
  }
  .card-title {
    font-family: 'Syne', sans-serif;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 6px;
  }
  .card-sub {
    color: var(--muted);
    font-size: 14px;
    margin-bottom: 28px;
    line-height: 1.5;
  }

  /* FORM ELEMENTS */
  label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--muted);
    letter-spacing: .3px;
    margin-bottom: 7px;
  }
  .field { margin-bottom: 20px; }

  input[type=text], input[type=number], select, textarea {
    width: 100%;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    padding: 13px 16px;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    -webkit-appearance: none;
  }
  input:focus, select:focus, textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(237, 238, 246, 0.15);
  }
  select option { background: #fefefe; }
  textarea { resize: vertical; min-height: 100px; line-height: 1.6; }

  .row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media(max-width:540px){ .row { grid-template-columns: 1fr; } }

  /* CHIP GROUP */
  .chips {
    display: flex; flex-wrap: wrap; gap: 8px;
  }
  .chip {
    padding: 7px 16px;
    border-radius: 100px;
    border: 1px solid var(--border);
    background: var(--surface2);
    color: var(--muted);
    font-size: 13px;
    cursor: pointer;
    transition: all .2s;
    user-select: none;
  }
  .chip:hover { border-color: var(--accent); color: var(--text); }
  .chip.selected {
    background: rgba(91,110,245,.18);
    border-color: var(--accent);
    color: var(--accent);
    font-weight: 500;
  }

  /* DIFFICULTY SLIDER VISUAL */
  .diff-row {
    display: grid; grid-template-columns: repeat(3,1fr); gap: 10px;
  }
  .diff-opt {
    padding: 12px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: var(--surface2);
    text-align: center;
    cursor: pointer;
    transition: all .2s;
  }
  .mode-opt {
    padding: 12px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: var(--surface2);
    text-align: center;
    cursor: pointer;
    transition: all .2s;
  }
  .diff-opt .emoji { font-size: 22px; display: block; margin-bottom: 4px; }
  .diff-opt .name { font-size: 12px; color: var(--muted); }
  .diff-opt.selected.easy   { border-color: var(--accent3); background: rgba(34,211,165,.1); }
  .diff-opt.selected.easy .name  { color: var(--accent3); }
  .diff-opt.selected.medium { border-color: var(--accent2); background: rgba(245,166,35,.1); }
  .diff-opt.selected.medium .name{ color: var(--accent2); }
  .diff-opt.selected.hard   { border-color: var(--danger); background: rgba(245,75,94,.1); }
  .diff-opt.selected.hard .name  { color: var(--danger); }

  .mode-opt .emoji { font-size: 22px; display: block; margin-bottom: 4px; }
  .mode-opt .name { font-size: 12px; color: var(--muted); }
  .mode-opt.selected.easy   { border-color: var(--accent3); background: rgba(34,211,165,.1); }
  .mode-opt.selected.easy .name  { color: var(--accent3); }
  .mode-opt.selected.medium { border-color: var(--accent2); background: rgba(245,166,35,.1); }
  .mode-opt.selected.medium .name{ color: var(--accent2); }
  .mode-opt.selected.hard   { border-color: var(--danger); background: rgba(245,75,94,.1); }
  .mode-opt.selected.hard .name  { color: var(--danger); }

  /* BUTTONS */
  .btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 13px 26px;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all .2s;
  }
  .btn-primary {
    background: var(--accent);
    color: #fff;
  }
  .btn-primary:hover { background: #6f80f7; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(91,110,245,.35); }
  .btn-primary:disabled { opacity: .45; cursor: not-allowed; transform: none; box-shadow: none; }
  .btn-outline {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--muted);
  }
  .btn-outline:hover { border-color: var(--accent); color: var(--text); }
  .btn-sm { padding: 8px 16px; font-size: 13px; border-radius: 8px; }

  .actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 8px; }

  /* LOADING */
  .loading-state {
    display: none;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    padding: 48px 0;
  }
  .loading-state.visible { display: flex; }
  .spinner {
    width: 52px; height: 52px;
    border: 3px solid var(--border);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  .loading-msg {
    font-family: 'Syne', sans-serif;
    font-size: 18px;
    color: var(--muted);
    text-align: center;
  }
  .loading-sub { font-size: 13px; color: var(--muted); opacity:.6; }

  /* QUESTION PREVIEW */
  .q-list { display: flex; flex-direction: column; gap: 14px; }
  .q-card {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    animation: fadeUp .4s ease both;
  }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .q-num {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .6px;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 6px;
  }
  .q-text {
    font-size: 15px;
    font-weight: 500;
    margin-bottom: 14px;
    line-height: 1.5;
  }
  .q-options { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
  .q-opt {
    padding: 9px 14px;
    border-radius: 8px;
    background: var(--surface);
    border: 1px solid var(--border);
    font-size: 13px;
    color: var(--muted);
  }
  .q-opt.correct {
    background: rgba(34,211,165,.1);
    border-color: var(--accent3);
    color: var(--accent3);
    font-weight: 500;
  }
  .q-meta {
    display: flex; gap: 10px; margin-top: 12px;
    flex-wrap: wrap;
  }
  .q-tag {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 100px;
    background: #fff;
    color: var(--accent);
  }

  /* SUCCESS / CODE */
  .success-box {
    background: var(--surface);
    border: 1px solid var(--accent3);
    border-radius: var(--radius);
    padding: 40px 32px;
    text-align: center;
    animation: fadeUp .5s ease;
  }
  .success-icon { font-size: 52px; margin-bottom: 16px; }
  .success-title {
    font-family: 'Syne', sans-serif;
    font-size: 26px;
    font-weight: 800;
    margin-bottom: 8px;
  }
  .success-sub { color: var(--muted); font-size: 14px; margin-bottom: 32px; }

  .code-display {
    display: inline-flex;
    align-items: center;
    gap: 16px;
    background: var(--surface2);
    border: 1px dashed var(--accent3);
    border-radius: 12px;
    padding: 18px 28px;
    margin-bottom: 32px;
  }
  .code-val {
    font-family: 'Syne', sans-serif;
    font-size: 42px;
    font-weight: 800;
    letter-spacing: 8px;
    color: var(--accent3);
  }
  .copy-btn {
    background: rgba(34,211,165,.15);
    border: 1px solid var(--accent3);
    color: var(--accent3);
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    cursor: pointer;
    transition: all .2s;
  }
  .copy-btn:hover { background: rgba(34,211,165,.25); }

  .exam-stats {
    display: flex; gap: 24px; justify-content: center;
    margin-bottom: 32px; flex-wrap: wrap;
  }
  .stat {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
  }
  .stat-val {
    font-family: 'Syne', sans-serif;
    font-size: 28px;
    font-weight: 700;
    color: var(--accent);
  }
  .stat-label { font-size: 12px; color: var(--muted); }

  /* SECTION TOGGLE */
  .section { display: none; }
  .section.active { display: block; }

  /* TOAST */
  .toast {
    position: fixed;
    bottom: 28px; left: 50%; transform: translateX(-50%) translateY(80px);
    background: var(--accent3);
    color: #0c0e14;
    font-weight: 600;
    font-size: 14px;
    padding: 12px 24px;
    border-radius: 100px;
    z-index: 999;
    transition: transform .3s cubic-bezier(.34,1.56,.64,1);
    pointer-events: none;
  }
  .toast.show { transform: translateX(-50%) translateY(0); }

  /* ERROR */
  .error-msg {
    color: var(--danger);
    font-size: 13px;
    margin-top: 8px;
    display: none;
  }
  .error-msg.visible { display: block; }
</style>
</head>
<body>
<div class="wrap">

  <!-- HEADER -->
  <header>
    <div class="badge">AI Powered</div>
  </header>

  <!-- STEPS -->
  <div class="steps" id="steps">
    <div class="step active" id="step1"><div class="step-dot">1</div><span class="step-label">Setup</span></div>
    <div class="step" id="step2"><div class="step-dot">2</div><span class="step-label">Generate</span></div>
    <div class="step" id="step3"><div class="step-dot">3</div><span class="step-label">Review</span></div>
    <div class="step" id="step4"><div class="step-dot">✓</div><span class="step-label">Publish</span></div>
  </div>

  <!-- ====== SECTION 1: SETUP ====== -->
  <div class="section active" id="sec1">
    <div class="card">
      <div class="card-title">Exam Setup</div>
      <div class="card-sub">Configure your exam details and let AI handle the rest.</div>

      <div class="field">
        <label>EXAM TITLE</label>
        <input type="text" id="examTitle" placeholder="e.g. Basic of electricity" />
      </div>

      <div class="row">
        <div class="field">
          <label>SUBJECT / TOPIC</label>
          <input type="text" id="examTopic" placeholder="e.g. Simple electricity concepts" />
        </div>
        <div class="field">
          <label>GRADE / LEVEL</label>
          <select id="examGrade">
            <option value="">— Select grade —</option>
            <option>Grade 1</option><option>Grade 2</option><option>Grade 3</option><option>Nursery 1</option>
            <option>Grade 4</option><option>Grade 5</option><option>Grade 6</option><option>Nursery 2</option>
            <option>Grade 7</option><option>Grade 8</option><option>Grade 9</option><option>Nursery 3</option>
            <option>Grade 10</option><option>Grade 11</option><option>Grade 12</option>
            <option>University — Undergraduate</option>
            <option>University — Graduate</option>
          </select>
        </div>
      </div>

      <div class="field">
        <label>ADDITIONAL CONTENT / NOTES (optional)</label>
        <textarea id="examContent" placeholder="Paste any syllabus notes, key concepts, or specific areas you want covered..."></textarea>
      </div>

      <div class="row">
        <div class="field">
          <label>NUMBER OF QUESTIONS</label>
          <select id="numQuestions">
            <option value="5">5 questions</option>
            <option value="8">8 questions</option>
            <option value="10" selected>10 questions</option>
            <option value="15">15 questions</option>
            <option value="20">20 questions</option>
          </select>
        </div>
        <div class="field">
          <label>TIME LIMIT PER QUESTION</label>
          <select id="timeLimit">
            <option value="15">15 seconds</option>
            <option value="20" selected>20 seconds</option>
            <option value="30">30 seconds</option>
            <option value="45">45 seconds</option>
            <option value="60">60 seconds</option>
          </select>
        </div>
      </div>

      <div class="field">
        <label>DIFFICULTY</label>
        <div class="diff-row">
          <div class="diff-opt easy" onclick="selectDiff(this,'easy')"><span class="emoji">🌱</span><span class="name">Easy</span></div>
          <div class="diff-opt medium selected" onclick="selectDiff(this,'medium')"><span class="emoji">⚡</span><span class="name">Medium</span></div>
          <div class="diff-opt hard" onclick="selectDiff(this,'hard')"><span class="emoji">🔥</span><span class="name">Hard</span></div>
        </div>
      </div>

      <div class="field">
        <label>QUESTION TYPES</label>
        <div class="chips">
          <div class="chip selected" onclick="toggleChip(this)">Multiple Choice</div>
          <div class="chip selected" onclick="toggleChip(this)">True / False</div>
          <div class="chip" onclick="toggleChip(this)">Fill in the Blank</div>
          <div class="chip" onclick="toggleChip(this)">Short Answer</div>
        </div>
      </div>
<div class="field" id="creationModeField">
  <label>How do you want to create your exam?</label>

  <div class="diff-row">

    <div class="mode-opt" onclick="selectMode(this,'ai')">
      <span class="emoji">🤖</span>
      <div>
        <div class="label">Generate with AI</div>
        <div class="name">Automatically create questions</div>
      </div>
    </div>

    <div class="mode-opt selected" onclick="selectMode(this,'upload')">
      <span class="emoji">📄</span>
      <div>
        <div class="label">Upload Excel File</div>
        <div class="name">Import questions from spreadsheet</div>
      </div>
    </div>

    <div class="mode-opt" onclick="selectMode(this,'manual')">
      <span class="emoji">✍️</span>
      <div>
        <div class="label">Create Manually</div>
        <div class="name">Add questions one by one</div>
      </div>
    </div>

  </div>
</div>

      <div class="error-msg" id="setupError">Please fill in the exam title, topic, and grade before continuing.</div>
    </div>
    <div class="actions">
      <button class="btn btn-primary" onclick="goGenerate()">Generate Questions →</button>
    </div>
  </div>

  <!-- ====== SECTION 2: GENERATING ====== -->
  <div class="section" id="sec2">
    <div class="card">
      <div class="loading-state visible">
        <div class="spinner"></div>
        <div>
          <div class="loading-msg" id="loadingMsg">Crafting your questions...</div>
          <div class="loading-sub" id="loadingSub">AI is reading your topic and building the exam</div>
        </div>
      </div>
    </div>
  </div>
  <!-- file uploading and manual creation sections would go here based on selected mode -->
   <div>
    <input type="file" id="fileInput" style="display:none;" accept=".xlsx,.xls" onchange="handleFileUpload(event)" />

   </div>

  <!-- ====== SECTION 3: REVIEW ====== -->
  <div class="section" id="sec3">
    <div class="card">
      <div class="card-title">Review Questions</div>
      <div class="card-sub">Check the AI-generated questions. You can regenerate if needed.</div>
      <div class="q-list" id="qList"></div>
    </div>
    <div class="actions">
      <button class="btn btn-outline" onclick="goSetup()">← Edit Setup</button>
      <button class="btn btn-outline" onclick="goGenerate()">↻ Regenerate</button>
      <button class="btn btn-primary" onclick="publishExam()">Publish Exam →</button>
    </div>
  </div>

  <!-- ====== SECTION 4: SUCCESS ====== -->
  <div class="section" id="sec4">
    <div class="success-box">
      <div class="success-icon">🎉</div>
      <div class="success-title">Exam Published!</div>
      <div class="success-sub">Share this code with your students to start the quiz.</div>
      <div style="display:flex;justify-content:center;margin-bottom:28px;">
        <div class="code-display">
          <div class="code-val" id="examCode">——</div>
          <button class="copy-btn" onclick="copyCode()">Copy</button>
        </div>
      </div>
      <div class="exam-stats" id="examStats"></div>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <button class="btn btn-outline" onclick="viewInDB()">📋 View in Database</button>
        <button class="btn btn-primary" onclick="resetAll()">+ Create Another Exam</button>
      </div>
    </div>

    <!-- Fake DB viewer -->
    <div id="dbSection" style="display:none;margin-top:20px;">
      <div class="card">
        <div class="card-title" style="font-size:15px;margin-bottom:16px;">📦 Exam Database Entry</div>
        <pre id="dbEntry" style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:18px;font-size:12px;color:var(--accent3);overflow-x:auto;white-space:pre-wrap;line-height:1.7;"></pre>
      </div>
    </div>
  </div>

</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script>
// ─── STATE ───────────────────────────────────────────────
let difficulty = 'medium';
let generatedQuestions = [];
let currentExamCode = '';
const examDB = {}; // in-memory "database"

// ─── STEP HELPERS ─────────────────────────────────────────
function setStep(n) {
  for(let i=1;i<=4;i++){
    const s = document.getElementById('step'+i);
    s.className = 'step' + (i<n?' done':i===n?' active':'');
    document.getElementById('sec'+i).className = 'section' + (i===n?' active':'');
  }
}

function goSetup() { setStep(1); }

// ─── UI HELPERS ──────────────────────────────────────────
function selectDiff(el, d) {
  document.querySelectorAll('.diff-opt').forEach(e => e.classList.remove('selected'));
  el.classList.add('selected');
  difficulty = d;
}

function toggleChip(el) {
  el.classList.toggle('selected');
}

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'), 2200);
}

function getSelectedChips() {
  return [...document.querySelectorAll('.chip.selected')].map(c=>c.textContent);
}

function generateCode() {
  const chars = '0123456789';
  return Array.from({length:6},()=>chars[Math.floor(Math.random()*chars.length)]).join('');
}

// ─── GENERATE ────────────────────────────────────────────
async function goGenerate() {
  const title   = document.getElementById('examTitle').value.trim();
  const topic   = document.getElementById('examTopic').value.trim();
  const grade   = document.getElementById('examGrade').value;
  const errEl   = document.getElementById('setupError');

  if(!title || !topic || !grade) {
    errEl.classList.add('visible');
    return;
  }
  errEl.classList.remove('visible');

  const content   = document.getElementById('examContent').value.trim();
  const numQ      = document.getElementById('numQuestions').value;
  const timeLimit = document.getElementById('timeLimit').value;
  const types     = getSelectedChips();

  setStep(2);

  // Cycle loading messages
  const msgs = [
    ['Analyzing your topic...','Reading the subject matter'],
    ['Crafting questions...','Building question logic and distractors'],
    ['Calibrating difficulty...','Tuning for ' + difficulty + ' level'],
    ['Finalizing your exam...','Almost ready!']
  ];
  let mi = 0;
  const msgEl = document.getElementById('loadingMsg');
  const subEl = document.getElementById('loadingSub');
  const cycleMsg = setInterval(()=>{
    mi = (mi+1)%msgs.length;
    msgEl.textContent = msgs[mi][0];
    subEl.textContent = msgs[mi][1];
  }, 1800);

  try {
    const typesStr = types.length ? types.join(', ') : 'Multiple Choice';
    const prompt = `You are an expert educator and quiz designer. Generate exactly ${numQ} quiz questions for the following exam.

Exam details:
- Title: ${title}
- Topic/Subject: ${topic}
- Grade/Level: ${grade}
- Difficulty: ${difficulty}
- Question Types: ${typesStr}
${content ? '- Additional context/notes: ' + content : ''}

Rules:
1. Each question must have exactly 4 answer options (A, B, C, D), even for True/False (use True, False, and 2 plausible distractors).
2. Clearly mark the correct answer.
3. Mix the question types if multiple types are requested.
4. For fill-in-the-blank, phrase as a question with 4 possible word/phrase options.
5. Make all distractors plausible but clearly wrong.
6. Match the difficulty level strictly.
7. Return ONLY a valid JSON array — no markdown, no explanation, no code fences.

JSON format (array of objects):
[
  {
    "question": "...",
    "options": ["A. ...", "B. ...", "C. ...", "D. ..."],
    "correct": 0,
    "type": "Multiple Choice",
    "points": 10
  }
]

"correct" is the 0-based index of the correct option.`;

    const response = await fetch("generateexams.php", {
     method: "POST",
     headers: {
         "Content-Type": "application/json"
    },
     body: JSON.stringify({ prompt })
});

    clearInterval(cycleMsg);
    const data = await response.json();
    console.log('AI response:', data);
    generatedQuestions = data.data;
    if(generatedQuestions.length === 0){
        buildFallback(topic, numQ, difficulty);
        setStep(3);
    }
    renderReview(generatedQuestions, title, grade, numQ, timeLimit);
    setStep(3);

  } catch(err) {
    clearInterval(cycleMsg);
    console.error(err);
    buildFallback(topic, numQ, difficulty);
    setStep(3);
    showToast('⚠️ Used offline sample questions');
  }
}

function buildFallback(topic, n, diff) {
  const qs = [];
  for(let i=0;i<parseInt(n);i++) {
    qs.push({
      question: `Sample question ${i+1} about ${topic}: Which of the following statements is correct?`,
      options: ['A. Statement Alpha','B. Statement Beta','C. Statement Gamma','D. Statement Delta'],
      correct: Math.floor(Math.random()*4),
      type: 'Multiple Choice',
      points: 10
    });
  }
  return qs;
}

// ─── RENDER REVIEW ────────────────────────────────────────
function renderReview(qs, title, grade, numQ, timeLimit) {
  const list = document.getElementById('qList');
  list.innerHTML = '';
  qs.forEach((q,i)=>{
    const div = document.createElement('div');
    div.className = 'q-card';
    div.style.animationDelay = (i*0.06)+'s';
    div.innerHTML = `
      <div class="q-num">Question ${i+1} of ${qs.length}</div>
      <div class="q-text">${q.question}</div>
      <div class="q-options">
        ${q.options.map((o,oi)=>`<div class="q-opt ${oi===q.correct?'correct':''}">${o}</div>`).join('')}
      </div>
      <div class="q-meta">
        <span class="q-tag">${q.type||'Multiple Choice'}</span>
        <span class="q-tag">${q.points||10} pts</span>
      </div>`;
    list.appendChild(div);
  });
}


// ─── PUBLISH ──────────────────────────────────────────────
function publishExam() {
  const title   = document.getElementById('examTitle').value.trim();
  const topic   = document.getElementById('examTopic').value.trim();
  const grade   = document.getElementById('examGrade').value;
  const numQ    = document.getElementById('numQuestions').value;
  const timeLimit = document.getElementById('timeLimit').value;

  currentExamCode = generateCode();
  const entry = {
    examCode: currentExamCode,
    title,
    topic,
    grade,
    difficulty,
    timePerQuestion: parseInt(timeLimit),
    totalQuestions: generatedQuestions.length,
    totalPoints: generatedQuestions.reduce((a,q)=>a+(q.points||10),0),
    createdAt: new Date().toISOString(),
    questions: generatedQuestions
  };
  examDB[currentExamCode] = entry;

  document.getElementById('examCode').textContent = currentExamCode;

  const stats = document.getElementById('examStats');
  stats.innerHTML = `
    <div class="stat"><div class="stat-val">${generatedQuestions.length}</div><div class="stat-label">Questions</div></div>
    <div class="stat"><div class="stat-val">${timeLimit}s</div><div class="stat-label">Per Question</div></div>
    <div class="stat"><div class="stat-val">${entry.totalPoints}</div><div class="stat-label">Total Points</div></div>
    <div class="stat"><div class="stat-val">${difficulty.charAt(0).toUpperCase()+difficulty.slice(1)}</div><div class="stat-label">Difficulty</div></div>
  `;

  document.getElementById('dbSection').style.display = 'none';
  setStep(4);
}

function viewInDB() {
  const entry = examDB[currentExamCode];
  document.getElementById('dbEntry').textContent = JSON.stringify(entry, null, 2);
  const sec = document.getElementById('dbSection');
  sec.style.display = sec.style.display === 'none' ? 'block' : 'none';
}

function copyCode() {
  navigator.clipboard.writeText(currentExamCode).then(()=>{
    showToast('✓ Code copied!');
  }).catch(()=>{
    showToast('Code: ' + currentExamCode);
  });
}

function resetAll() {
  document.getElementById('examTitle').value = '';
  document.getElementById('examTopic').value = '';
  document.getElementById('examGrade').value = '';
  document.getElementById('examContent').value = '';
  document.getElementById('dbSection').style.display = 'none';
  generatedQuestions = [];
  setStep(1);
}
</script>
<script>
function upload(){
   document.getElementById('fileInput').click();
}
function selectMode(el, mode) {
  document.querySelectorAll('.mode-opt').forEach(d => d.classList.remove('selected'));
  el.classList.add('selected');

  const info = document.getElementById("creationModeField");

  
  if (mode === 'upload') {
    setStep(2);
    upload();
    return;
  }

  if (mode === 'manual') {
    setStep(3)
    startManual();
  }
}


function showUpload() {
  alert("Show file upload input here");
}

function startManual() {
  document.getElementById('loadingMsg').textContent = 'Manual creation mode coming soon!';
}
</script>
</body>
</html>