<?php
require_once('../db_connection.php');
require_once(__DIR__ . '/lib/exam_acl.php');
require_once(__DIR__ . '/lib/exam_settings.php');

$acl = exam_acl_context($conn);
if (!$acl['user_id']) {
    header('Location: ../index.php');
    exit;
}

// ==============================
// FETCH EXAM (optionally by ?exam_id=X, else latest owned by me)
// ==============================
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

if ($exam_id) {
    // School-scoped view gate. Developers bypass.
    exam_acl_require_view($conn, $exam_id);
    $stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ? LIMIT 1");
    $stmt->bind_param("i", $exam_id);
} elseif ($acl['is_developer']) {
    $stmt = $conn->prepare("SELECT * FROM exams ORDER BY created_at DESC LIMIT 1");
} else {
    // Latest exam visible to me: my own OR tagged for my school.
    $stmt = $conn->prepare(
        "SELECT e.*
           FROM exams e
          WHERE e.created_by = ?
             OR (? > 0 AND e.school_id = ?)
          ORDER BY e.created_at DESC LIMIT 1"
    );
    $school_id = (int)($acl['school_id'] ?? 0);
    $stmt->bind_param("iii", $acl['user_id'], $school_id, $school_id);
}
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) { echo "You have not published any exams yet. <a href='exam_creator_working.php'>Create one</a>."; exit(); }

$exam_id     = $exam['exam_id'];
$exam_title  = htmlspecialchars($exam['title']);
$exam_topic  = htmlspecialchars($exam['topic']);
$exam_grade  = htmlspecialchars($exam['grade']);
$exam_status = $exam['status'];
$exam_code   = $exam['exam_code'] ?? '';

// ==============================
// FILTERS
// ==============================
$filter_grade  = $_GET['grade']  ?? '';
$filter_stream = $_GET['stream'] ?? '';
$filter_school = $_GET['school'] ?? '';
$filter_result = $_GET['result'] ?? '';
$filter_session = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
$filter_search = trim($_GET['search'] ?? '');

// ==============================
// TOTAL MARKS
// ==============================
$stmt3 = $conn->prepare("SELECT SUM(marks) AS total_marks FROM questions WHERE exam_id = ?");
$stmt3->bind_param("i", $exam_id);
$stmt3->execute();
$total_marks = $stmt3->get_result()->fetch_assoc()['total_marks'] ?? 0;

// ==============================
// FETCH ALL EXAMS for dropdown (scoped to current teacher + same-school colleagues)
// ==============================
if ($acl['is_developer']) {
    $all_exams = $conn->query(
        "SELECT e.exam_id, e.title, e.created_by, COALESCE(CONCAT(u.firstname,' ',u.lastname), '') AS owner_name
           FROM exams e
           LEFT JOIN users u ON u.user_id = e.created_by
          ORDER BY e.created_at DESC"
    );
} else {
    $ae_stmt = $conn->prepare(
        "SELECT e.exam_id, e.title, e.created_by, COALESCE(CONCAT(u.firstname,' ',u.lastname), '') AS owner_name
           FROM exams e
           LEFT JOIN users u ON u.user_id = e.created_by
          WHERE e.created_by = ?
             OR (? > 0 AND e.school_id = ?)
          ORDER BY e.created_at DESC"
    );
    $school_id = (int)($acl['school_id'] ?? 0);
    $ae_stmt->bind_param("iii", $acl['user_id'], $school_id, $school_id);
    $ae_stmt->execute();
    $all_exams = $ae_stmt->get_result();
}

// ==============================
// FETCH PLAYERS (with filters)
// ==============================
$sql = "SELECT * FROM players WHERE exam_id = ?";
$params = [$exam_id];
$types  = "i";
if ($filter_session > 0) {
    $sql .= " AND session_id = ?";
    $types .= "i";
    $params[] = $filter_session;
}

if ($filter_grade)  { $sql .= " AND grade = ?";   $types .= "s"; $params[] = $filter_grade; }
if ($filter_stream) { $sql .= " AND stream = ?";  $types .= "s"; $params[] = $filter_stream; }
if ($filter_school) { $sql .= " AND school = ?";  $types .= "s"; $params[] = $filter_school; }
if ($filter_search !== '') {
    // Match the student name in either column:
    //  - nickname        → solo students + group teammates who got their own row (waiting_room.php path)
    //  - group_members   → teammates listed only on the group leader's row (waiting.php legacy path)
    $sql   .= " AND (nickname LIKE ? OR COALESCE(group_members,'') LIKE ?)";
    $types .= "ss";
    $like   = "%" . $filter_search . "%";
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY score DESC";
$pstmt = $conn->prepare($sql);
$pstmt->bind_param($types, ...$params);
$pstmt->execute();
$all_players = $pstmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Apply pass/fail filter in PHP (needs total_marks)
if ($filter_result === 'pass') {
    $all_players = array_filter($all_players, fn($p) => $total_marks > 0 && ($p['score'] / $total_marks) >= 0.5);
} elseif ($filter_result === 'fail') {
    $all_players = array_filter($all_players, fn($p) => $total_marks == 0 || ($p['score'] / $total_marks) < 0.5);
}

// Unique grades and schools for filter dropdowns
$grades_stmt  = $conn->prepare("SELECT DISTINCT grade FROM players WHERE exam_id = ? ORDER BY grade");
$grades_stmt->bind_param("i", $exam_id);
$grades_stmt->execute();
$distinct_grades = $grades_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$schools_stmt = $conn->prepare("SELECT DISTINCT school FROM players WHERE exam_id = ? ORDER BY school");
$schools_stmt->bind_param("i", $exam_id);
$schools_stmt->execute();
$distinct_schools = $schools_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$streams_stmt = $conn->prepare("SELECT DISTINCT stream FROM players WHERE exam_id = ? AND stream IS NOT NULL AND stream <> '' ORDER BY stream");
$streams_stmt->bind_param("i", $exam_id);
$streams_stmt->execute();
$distinct_streams = $streams_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Exam Reports</title>
<link rel="stylesheet" href="../dist/styles.css">
    <link rel="stylesheet" href="<?= APP_BASE_URL ?>/exams/assets/exam-theme.css">
    <style>
        /* Match the dark glass exam-theme used everywhere else.
           Overrides Tailwind `bg-white` and gray text classes so cards blend with the
           dark page background instead of fighting it. */
        .exam-glass {
            background: rgba(255,255,255,.05) !important;
            border: 1px solid rgba(168,85,247,.3) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            color: #f1f5f9 !important;
            box-shadow: 0 8px 24px rgba(0,0,0,.25) !important;
        }
        .exam-glass .text-gray-500 { color: #cbd5e1 !important; }
        .exam-glass .text-gray-800 { color: #ffffff !important; }
        .exam-glass .text-blue-600 { color: #93c5fd !important; }
        .exam-glass table {
            background: transparent !important;
            backdrop-filter: none !important;
            border: none !important;
        }
        .exam-glass thead th {
            background: rgba(124,58,237,.18) !important;
            color: #cbd5e1 !important;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 700 !important;
            border-bottom: 1px solid rgba(168,85,247,.3) !important;
        }
        .exam-glass td {
            color: #f1f5f9 !important;
            border-bottom: 1px solid rgba(255,255,255,.06) !important;
            padding: 12px 24px !important;
        }
        .exam-glass tbody tr:hover { background: rgba(168,85,247,.08) !important; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen exam-dark">
<?php include('../Auth/SF/header.php'); ?>

<div class="flex flex-1 overflow-hidden">

    <!-- SIDEBAR -->
    <div class="bg-white border-r border-gray-200 min-h-screen w-64 hidden md:block">
        <?php include('./dynamic_sidebar.php'); ?>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 p-8 overflow-y-auto">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-xl font-semibold" style="color:#fff">Exam Reports</h1>
                <p class="text-sm" style="color:#cbd5e1">Scores and performance overview</p>
            </div>
            <div class="flex gap-3">
                <a href="grade_practical.php?exam_id=<?= $exam_id ?>"
                class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm rounded-lg shadow">
               🎓 Grade Open-Ended
               </a>
                <button type="button" onclick="cleanupUnfinished(<?= (int)$exam_id ?>)"
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm rounded-lg shadow">
                    🧹 Clean Unfinished
                </button>
                <a href="question_report.php?exam_id=<?= $exam_id ?>"
                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg shadow">
                    📊 Per-question report
                </a>
                <a href="exam_creator_working.php"
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg shadow">
                    + Create Exam
                </a>
                <a href="exam_reports.php?exam_id=<?= $exam_id ?>"
                   class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg shadow">
                    Download Excel
                </a>
            </div>
        </div>

        <script>
            // Removes player rows that joined the exam but never submitted any
            // answer (typical cause of duplicate empty/zero-score rows when a
            // student re-clicks "Take Exam"). Group named members of REAL
            // submissions are preserved — see cleanup_players.php for the
            // exact rule.
            async function cleanupUnfinished(examId) {
                if (!confirm("This deletes players who joined but never submitted any answer.\n\nGroup teammates of real submissions stay safe.\n\nProceed?")) return;
                try {
                    const fd = new FormData();
                    fd.append('exam_id', examId);
                    const r = await fetch('cleanup_players.php', { method: 'POST', body: fd });
                    const j = await r.json();
                    if (j.success) {
                        alert(j.message || ('Removed ' + j.removed));
                        location.reload();
                    } else {
                        alert('Cleanup failed: ' + (j.error || 'unknown'));
                    }
                } catch (err) {
                    alert('Cleanup error: ' + err.message);
                }
            }
        </script>

        <!-- Exam Switcher -->
        <div class="mb-6">
            <form method="GET" class="flex items-center gap-3">
                <label class="text-sm text-gray-600">Viewing exam:</label>
                <select name="exam_id" onchange="this.form.submit()"
                        class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                    <?php while ($e = $all_exams->fetch_assoc()):
                        $is_mine = ((int)$e['created_by'] === (int)$acl['user_id']);
                        $tag = $is_mine ? '' : ' — by ' . trim($e['owner_name'] ?: 'colleague');
                    ?>
                        <option value="<?= $e['exam_id'] ?>"
                            <?= $e['exam_id'] == $exam_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['title'] . $tag) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border exam-glass">
                <p class="text-xs text-gray-500 mb-1">Exam title</p>
                <p class="font-semibold text-gray-800"><?= $exam_title ?></p>
            </div>
            <div class="bg-white p-4 rounded-xl border exam-glass">
                <p class="text-xs text-gray-500 mb-1">Topic</p>
                <p class="font-semibold text-gray-800"><?= $exam_topic ?></p>
            </div>
            <div class="bg-white p-4 rounded-xl border exam-glass">
                <p class="text-xs text-gray-500 mb-1">Grade</p>
                <p class="font-semibold text-gray-800"><?= $exam_grade ?></p>
            </div>
            <div class="bg-white p-4 rounded-xl border exam-glass">
                <p class="text-xs text-gray-500 mb-1">Total marks</p>
                <p class="font-semibold text-blue-600"><?= $total_marks ?></p>
            </div>
        </div>

        <!-- Status + Exam Code -->
        <div class="flex items-center gap-4 mb-6">
            <?php if ($exam_status === 'active'): ?>
                <span class="px-3 py-1 text-sm rounded-full font-bold"
                      style="background:rgba(34,197,94,.18);color:#86efac;border:1px solid rgba(34,197,94,.4);text-transform:uppercase;letter-spacing:1px">
                    <?= ucfirst($exam_status) ?>
                </span>
            <?php else: ?>
                <span class="px-3 py-1 text-sm rounded-full font-bold"
                      style="background:rgba(148,163,184,.18);color:#cbd5e1;border:1px solid rgba(148,163,184,.3);text-transform:uppercase;letter-spacing:1px">
                    <?= ucfirst($exam_status) ?>
                </span>
            <?php endif; ?>
            <?php if ($exam_code): ?>
                <span class="text-sm" style="color:#cbd5e1">
                    Exam code: <strong style="color:#fff;font-family:'Courier New',monospace;background:rgba(168,85,247,.18);padding:3px 8px;border-radius:6px"><?= htmlspecialchars($exam_code) ?></strong>
                </span>
            <?php endif; ?>
        </div>

        <!-- Per-exam permission toggles. Click to flip; saved via AJAX. -->
        <?php
            $perm_allow_replay   = (int)exam_get_setting($conn, $exam_id, 'allow_replay', 1);
            $perm_show_answers   = (int)exam_get_setting($conn, $exam_id, 'show_answers_to_student', 1);
        ?>
        <div class="exam-perms mb-6" style="background:rgba(255,255,255,.05);border:1px solid rgba(168,85,247,.3);border-radius:12px;padding:14px 18px;display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
            <span style="color:#cbd5e1;font-size:12px;font-weight:800;letter-spacing:1px;text-transform:uppercase;">🛡️ Permissions</span>

            <button type="button"
                    class="perm-pill <?= $perm_allow_replay ? 'on' : 'off' ?>"
                    data-key="allow_replay"
                    data-exam="<?= (int)$exam_id ?>"
                    data-value="<?= $perm_allow_replay ?>"
                    title="When OFF, a student who has already submitted this exam is redirected to their existing results instead of getting a fresh attempt."
                    style="cursor:pointer;border:none;padding:7px 14px;border-radius:99px;font-weight:700;font-size:13px;letter-spacing:.3px;">
                🔁 Allow retake:
                <strong class="perm-state"><?= $perm_allow_replay ? 'ON' : 'OFF' ?></strong>
            </button>

            <button type="button"
                    class="perm-pill <?= $perm_show_answers ? 'on' : 'off' ?>"
                    data-key="show_answers_to_student"
                    data-exam="<?= (int)$exam_id ?>"
                    data-value="<?= $perm_show_answers ?>"
                    title="When OFF, students only see their score and rank — not the per-question breakdown. Useful so the first finishers can't share answers with classmates still in the exam."
                    style="cursor:pointer;border:none;padding:7px 14px;border-radius:99px;font-weight:700;font-size:13px;letter-spacing:.3px;">
                👁️ Show answers to student:
                <strong class="perm-state"><?= $perm_show_answers ? 'ON' : 'OFF' ?></strong>
            </button>

            <span style="color:#94a3b8;font-size:12px;margin-left:auto;">Click a pill to toggle. Saved instantly.</span>
        </div>

        <style>
            .perm-pill.on  { background:rgba(34,197,94,.18); color:#86efac; border:1px solid rgba(34,197,94,.45) !important; }
            .perm-pill.off { background:rgba(239,68,68,.18); color:#fca5a5; border:1px solid rgba(239,68,68,.45) !important; }
            .perm-pill:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.2); }
            .perm-pill:disabled { opacity:.5; cursor:wait; }
        </style>

        <script>
        // Toggle a per-exam permission via AJAX. Falls back to a full reload
        // if the server reports trouble, so the user is never confused about
        // the current state.
        document.querySelectorAll('.perm-pill').forEach((btn) => {
            btn.addEventListener('click', async () => {
                if (btn.disabled) return;
                const examId  = parseInt(btn.dataset.exam, 10);
                const key     = btn.dataset.key;
                const current = parseInt(btn.dataset.value, 10) ? 1 : 0;
                const next    = current ? 0 : 1;

                btn.disabled = true;
                btn.querySelector('.perm-state').textContent = '…';
                try {
                    const fd = new FormData();
                    fd.append('exam_id', examId);
                    fd.append('key', key);
                    fd.append('value', next);
                    const r = await fetch('exam_settings_save.php', { method: 'POST', body: fd });
                    const j = await r.json();
                    if (!j.success) throw new Error(j.error || 'save failed');
                    btn.dataset.value = next;
                    btn.classList.toggle('on',  next === 1);
                    btn.classList.toggle('off', next === 0);
                    btn.querySelector('.perm-state').textContent = next ? 'ON' : 'OFF';
                } catch (err) {
                    alert('Could not save: ' + err.message);
                    btn.querySelector('.perm-state').textContent = current ? 'ON' : 'OFF';
                } finally {
                    btn.disabled = false;
                }
            });
        });

        // Per-row Reset attempt (clears a player's answers + score so they can retake).
        async function resetPlayer(examId, playerId, studentName) {
            if (!confirm(`Reset ${studentName || 'this student'}'s attempt?\n\nTheir answers will be deleted and score zeroed. They will be able to retake this exam even if "Allow retake" is OFF.`)) return;
            try {
                const fd = new FormData();
                fd.append('exam_id', examId);
                fd.append('player_id', playerId);
                const r = await fetch('reset_player.php', { method: 'POST', body: fd });
                const j = await r.json();
                if (j.success) {
                    alert(j.message || 'Attempt cleared.');
                    location.reload();
                } else {
                    alert('Reset failed: ' + (j.error || 'unknown'));
                }
            } catch (err) {
                alert('Reset error: ' + err.message);
            }
        }
        </script>

        <!-- Filters -->
        <form method="GET" class="flex flex-wrap gap-3 items-center mb-5">
            <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
           <!-- ⬇️ PASTE SESSION DROPDOWN HERE ⬇️ -->
    <?php
    $sess_stmt = $conn->prepare("SELECT session_id, session_code, session_label, created_at FROM exam_sessions WHERE exam_id = ? ORDER BY created_at DESC");
    $sess_stmt->bind_param("i", $exam_id);
    $sess_stmt->execute();
    $sessions = $sess_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    ?>
    <?php if (count($sessions) > 0): ?>
    <select name="session_id" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
        <option value="0">All Sessions / Classes</option>
        <?php foreach ($sessions as $s): ?>
            <option value="<?= $s['session_id'] ?>" <?= $filter_session == $s['session_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['session_label']) ?> (Code: <?= $s['session_code'] ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <!-- ⬆️ END SESSION DROPDOWN ⬆️ -->
            <select name="grade" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                <option value="">All grades</option>
                <?php foreach ($distinct_grades as $g): ?>
                    <option value="<?= htmlspecialchars($g['grade']) ?>"
                        <?= $filter_grade === $g['grade'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['grade']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Streams are fixed A–E across the whole platform (student picker
                 in exams/stream.php + exams/select_mode.php only offers A–E). -->
            <select name="stream" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                <option value="">All streams</option>
                <?php foreach (['A','B','C','D','E'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filter_stream === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>

            <select name="school" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                <option value="">All schools</option>
                <?php foreach ($distinct_schools as $s): ?>
                    <option value="<?= htmlspecialchars($s['school']) ?>"
                        <?= $filter_school === $s['school'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['school']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="result" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                <option value="">All results</option>
                <option value="pass" <?= $filter_result === 'pass' ? 'selected' : '' ?>>Pass only</option>
                <option value="fail" <?= $filter_result === 'fail' ? 'selected' : '' ?>>Fail only</option>
            </select>

            <input type="text" name="search" value="<?= htmlspecialchars($filter_search) ?>"
                   placeholder="Search student name..."
                   class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">

            <button type="submit"
                    class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded">
                Filter
            </button>

            <?php if ($filter_grade || $filter_stream || $filter_school || $filter_result || $filter_search): ?>
                <a href="?exam_id=<?= $exam_id ?>"
                   class="text-sm text-gray-500 hover:text-red-500">Clear filters</a>
            <?php endif; ?>
        </form>

        <!-- Leaderboard Table -->
        <div class="bg-white rounded-xl border exam-glass">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h2 class="font-semibold" style="color:#ffffff">Leaderboard &amp; reports</h2>
                <span class="text-sm" style="color:#cbd5e1"><?= count($all_players) ?> player(s)</span>
            </div>

            <?php if (count($all_players) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b text-xs uppercase tracking-wide">
                            <th class="px-6 py-3">#</th>
                            <th class="px-6 py-3">Player</th>
                            <th class="px-6 py-3">Score</th>
                            <th class="px-6 py-3">%</th>
                            <th class="px-6 py-3">Progress</th>
                            <th class="px-6 py-3">Grade</th>
                            <th class="px-6 py-3">Stream</th>
                            <th class="px-6 py-3">School</th>
                            <th class="px-6 py-3">Result</th>
                            <th class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $rank = 1;
                    foreach ($all_players as $row):
                        $score      = $row['score'];
                        $pct        = ($total_marks > 0) ? round(($score / $total_marks) * 100) : 0;
                        $pass       = $pct >= 50;
                        $bar_color  = $pct >= 50 ? 'bg-blue-500' : 'bg-red-400';
                    ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-3 font-semibold text-gray-500"><?= $rank++ ?></td>
                            <td class="px-6 py-3"><?= htmlspecialchars($row['nickname']) ?></td>
                            <td class="px-6 py-3 font-semibold text-blue-600"><?= $score ?> / <?= $total_marks ?></td>
                            <td class="px-6 py-3"><?= $pct ?>%</td>
                            <td class="px-6 py-3">
                                <div class="w-20 bg-gray-100 rounded-full h-1.5">
                                    <div class="<?= $bar_color ?> h-1.5 rounded-full"
                                         style="width:<?= $pct ?>%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-3"><?= htmlspecialchars($row['grade']) ?></td>
                            <td class="px-6 py-3"><?= htmlspecialchars($row['stream'] ?? '') ?></td>
                            <td class="px-6 py-3"><?= htmlspecialchars($row['school']) ?></td>
                            <td class="px-6 py-3">
                                <?php if ($pass): ?>
                                    <span class="rounded-full text-xs font-bold"
                                          style="display:inline-flex;align-items:center;gap:4px;white-space:nowrap;padding:4px 12px;background:rgba(34,197,94,.18);color:#86efac;border:1px solid rgba(34,197,94,.45);text-transform:uppercase;letter-spacing:1px">
                                        ✓ Pass
                                    </span>
                                <?php else: ?>
                                    <span class="rounded-full text-xs font-bold"
                                          style="display:inline-flex;align-items:center;gap:4px;white-space:nowrap;padding:4px 12px;background:rgba(239,68,68,.18);color:#fca5a5;border:1px solid rgba(239,68,68,.45);text-transform:uppercase;letter-spacing:1px">
                                        ✗ Fail
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3">
                                <button type="button"
                                        onclick="resetPlayer(<?= (int)$exam_id ?>, <?= (int)$row['player_id'] ?>, <?= json_encode($row['nickname'] ?: 'this student') ?>)"
                                        class="text-xs font-bold px-3 py-1 rounded"
                                        style="background:rgba(245,158,11,.18);color:#fde68a;border:1px solid rgba(245,158,11,.45);"
                                        title="Clear this student's answers + score so they can retake (overrides the Allow-Retake setting)">
                                    🔄 Reset
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>    
            </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <p class="text-gray-400">No players match your filters.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include('../Auth/SF/footer.php'); ?>
</body>
</html>
<?php // Connection closed by db_connection.php shutdown handler ?>