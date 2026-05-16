<?php
/**
 * Per-question analytics for an exam.
 *
 * Views (via ?view=...):
 *   summary  — one row per question: attempted / correct / wrong / % correct
 *   matrix   — students (rows) x questions (cols), ✓/✗/— per cell
 *   student  — drill-in for a single player: question + correct answer + their answer
 *
 * Filters: session_id, grade, stream, school, search (player name)
 * Export: ?format=xls streams the matrix as an Excel-friendly .xls
 *
 * ACL: exam_acl_require_view — owner, same-school facilitator, or Developer.
 */

require_once('../db_connection.php');
require_once(__DIR__ . '/lib/exam_acl.php');

$acl = exam_acl_context($conn);
if (!$acl['user_id']) {
    header('Location: ../index.php');
    exit;
}

// ==============================
// Resolve exam_id (explicit, else latest visible)
// ==============================
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

if ($exam_id > 0) {
    exam_acl_require_view($conn, $exam_id);
    $stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ? LIMIT 1");
    $stmt->bind_param('i', $exam_id);
} elseif ($acl['is_developer']) {
    $stmt = $conn->prepare("SELECT * FROM exams ORDER BY created_at DESC LIMIT 1");
} else {
    $stmt = $conn->prepare(
        "SELECT e.*
           FROM exams e
          WHERE e.created_by = ?
             OR (? > 0 AND e.school_id = ?)
          ORDER BY e.created_at DESC LIMIT 1"
    );
    $school_id = (int)($acl['school_id'] ?? 0);
    $stmt->bind_param('iii', $acl['user_id'], $school_id, $school_id);
}
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exam) {
    echo "No exams available for your account. <a href='exams_dashboard.php'>Back to dashboard</a>.";
    exit;
}

$exam_id     = (int)$exam['exam_id'];
$exam_title  = htmlspecialchars($exam['title']);
$exam_topic  = htmlspecialchars($exam['topic']);
$exam_grade  = htmlspecialchars($exam['grade']);
$exam_code   = $exam['exam_code'] ?? '';

// ==============================
// Filters
// ==============================
$filter_grade   = $_GET['grade']   ?? '';
$filter_stream  = $_GET['stream']  ?? '';
$filter_school  = $_GET['school']  ?? '';
$filter_search  = $_GET['search']  ?? '';
$filter_session = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
$view           = $_GET['view']    ?? 'summary';
$drill_player   = isset($_GET['player_id']) ? (int)$_GET['player_id'] : 0;
$format         = $_GET['format']  ?? '';

// ==============================
// Exam switcher dropdown (visible exams: own + same school)
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
    $ae_stmt->bind_param('iii', $acl['user_id'], $school_id, $school_id);
    $ae_stmt->execute();
    $all_exams = $ae_stmt->get_result();
}

// ==============================
// Questions (ordered) — used by every view
// ==============================
$qstmt = $conn->prepare(
    "SELECT question_id, question_text, question_type, marks
       FROM questions
      WHERE exam_id = ?
      ORDER BY question_id ASC"
);
$qstmt->bind_param('i', $exam_id);
$qstmt->execute();
$questions = $qstmt->get_result()->fetch_all(MYSQLI_ASSOC);
$qstmt->close();

$total_marks = 0;
foreach ($questions as $q) { $total_marks += (int)$q['marks']; }

// ==============================
// Build the players filter (shared by all views)
// ==============================
$psql = "SELECT player_id, nickname, score, grade, COALESCE(stream,'') AS stream, school
           FROM players
          WHERE exam_id = ?";
$ptypes  = 'i';
$pparams = [$exam_id];
if ($filter_session > 0) { $psql .= " AND session_id = ?"; $ptypes .= 'i'; $pparams[] = $filter_session; }
if ($filter_grade)       { $psql .= " AND grade = ?";      $ptypes .= 's'; $pparams[] = $filter_grade; }
if ($filter_stream)      { $psql .= " AND stream = ?";     $ptypes .= 's'; $pparams[] = $filter_stream; }
if ($filter_school)      { $psql .= " AND school = ?";     $ptypes .= 's'; $pparams[] = $filter_school; }
if ($filter_search)      { $psql .= " AND nickname LIKE ?"; $ptypes .= 's'; $pparams[] = '%' . $filter_search . '%'; }
$psql .= " ORDER BY score DESC, player_id ASC";

$pstmt = $conn->prepare($psql);
$pstmt->bind_param($ptypes, ...$pparams);
$pstmt->execute();
$players = $pstmt->get_result()->fetch_all(MYSQLI_ASSOC);
$pstmt->close();

$player_ids = array_map(fn($p) => (int)$p['player_id'], $players);

// ==============================
// Pull answers for the filtered player set
// ==============================
$answers_by_player_q = []; // [$player_id][$question_id] = ['is_correct' => 0|1, 'chosen' => '...', 'points' => N]
if (!empty($player_ids)) {
    $in = implode(',', array_fill(0, count($player_ids), '?'));
    $atypes  = 'i' . str_repeat('i', count($player_ids));
    $aparams = array_merge([$exam_id], $player_ids);
    $astmt = $conn->prepare(
        "SELECT player_id, question_id, chosen_answer, is_correct, points_earned
           FROM answers
          WHERE exam_id = ? AND player_id IN ($in)"
    );
    $astmt->bind_param($atypes, ...$aparams);
    $astmt->execute();
    $ares = $astmt->get_result();
    while ($r = $ares->fetch_assoc()) {
        $answers_by_player_q[(int)$r['player_id']][(int)$r['question_id']] = [
            'is_correct' => (int)$r['is_correct'],
            'chosen'     => (string)$r['chosen_answer'],
            'points'     => (int)$r['points_earned'],
        ];
    }
    $astmt->close();
}

// ==============================
// Dropdown sources
// ==============================
$grades_stmt = $conn->prepare("SELECT DISTINCT grade FROM players WHERE exam_id = ? ORDER BY grade");
$grades_stmt->bind_param('i', $exam_id); $grades_stmt->execute();
$distinct_grades = $grades_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$schools_stmt = $conn->prepare("SELECT DISTINCT school FROM players WHERE exam_id = ? ORDER BY school");
$schools_stmt->bind_param('i', $exam_id); $schools_stmt->execute();
$distinct_schools = $schools_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$streams_stmt = $conn->prepare("SELECT DISTINCT stream FROM players WHERE exam_id = ? AND stream IS NOT NULL AND stream <> '' ORDER BY stream");
$streams_stmt->bind_param('i', $exam_id); $streams_stmt->execute();
$distinct_streams = $streams_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$sess_stmt = $conn->prepare("SELECT session_id, session_code, session_label FROM exam_sessions WHERE exam_id = ? ORDER BY created_at DESC");
$sess_stmt->bind_param('i', $exam_id); $sess_stmt->execute();
$sessions = $sess_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ==============================
// Aggregate per question
// ==============================
$q_stats = [];  // [$question_id] => ['attempted' => N, 'correct' => N, 'wrong' => N]
foreach ($questions as $q) {
    $qid = (int)$q['question_id'];
    $q_stats[$qid] = ['attempted' => 0, 'correct' => 0, 'wrong' => 0];
}
foreach ($answers_by_player_q as $pid => $per_q) {
    foreach ($per_q as $qid => $a) {
        if (!isset($q_stats[$qid])) continue;
        $hadAnswer = $a['chosen'] !== '' || $a['is_correct'] || $a['points'] > 0;
        if ($hadAnswer) $q_stats[$qid]['attempted']++;
        if ($a['is_correct']) $q_stats[$qid]['correct']++;
        elseif ($hadAnswer)   $q_stats[$qid]['wrong']++;
    }
}

// ==============================
// Excel export (matrix)
// ==============================
if ($format === 'xls') {
    $safe = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $exam['title']) ?: ('exam_' . $exam_id);
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $safe . '_question_matrix.xls"');
    header('Cache-Control: max-age=0');
    echo "<table border='1'>";
    echo "<tr><th>#</th><th>Student</th><th>Grade</th><th>Stream</th><th>School</th><th>Score</th>";
    foreach ($questions as $q) {
        echo '<th>Q' . (int)$q['question_id'] . ' (' . (int)$q['marks'] . ' pts)</th>';
    }
    echo "<th>Total</th></tr>";
    $rank = 1;
    foreach ($players as $p) {
        echo '<tr>';
        echo '<td>' . ($rank++) . '</td>';
        echo '<td>' . htmlspecialchars($p['nickname']) . '</td>';
        echo '<td>' . htmlspecialchars($p['grade']) . '</td>';
        echo '<td>' . htmlspecialchars($p['stream']) . '</td>';
        echo '<td>' . htmlspecialchars($p['school']) . '</td>';
        echo '<td>' . (int)$p['score'] . ' / ' . $total_marks . '</td>';
        foreach ($questions as $q) {
            $a = $answers_by_player_q[(int)$p['player_id']][(int)$q['question_id']] ?? null;
            if (!$a || ($a['chosen'] === '' && !$a['is_correct'])) {
                echo '<td>-</td>';
            } elseif ($a['is_correct']) {
                echo '<td>OK (' . $a['points'] . ')</td>';
            } else {
                echo '<td>X</td>';
            }
        }
        echo '<td>' . (int)$p['score'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    exit;
}

// ==============================
// Student deep-dive (separate fetch — pulls correct answers too)
// ==============================
$drill_data = null;
if ($view === 'student' && $drill_player > 0) {
    // Make sure this player is in the visible exam.
    $vchk = $conn->prepare("SELECT player_id, nickname, score, grade, COALESCE(stream,'') AS stream, school FROM players WHERE player_id = ? AND exam_id = ? LIMIT 1");
    $vchk->bind_param('ii', $drill_player, $exam_id);
    $vchk->execute();
    $drill_player_row = $vchk->get_result()->fetch_assoc();
    $vchk->close();

    if ($drill_player_row) {
        // Correct answers (concat all is_correct=1 options per question)
        $correct_map = [];
        $cstmt = $conn->prepare(
            "SELECT q.question_id, q.question_text, q.question_type, q.marks,
                    GROUP_CONCAT(o.option_text SEPARATOR ' | ') AS correct_text
               FROM questions q
               LEFT JOIN options o ON o.question_id = q.question_id AND o.is_correct = 1
              WHERE q.exam_id = ?
              GROUP BY q.question_id
              ORDER BY q.question_id ASC"
        );
        $cstmt->bind_param('i', $exam_id);
        $cstmt->execute();
        $qres = $cstmt->get_result();
        while ($qr = $qres->fetch_assoc()) {
            $correct_map[(int)$qr['question_id']] = $qr;
        }
        $cstmt->close();

        // The student's answers
        $sa_stmt = $conn->prepare(
            "SELECT question_id, chosen_answer, is_correct, points_earned
               FROM answers
              WHERE player_id = ? AND exam_id = ?"
        );
        $sa_stmt->bind_param('ii', $drill_player, $exam_id);
        $sa_stmt->execute();
        $sa_res = $sa_stmt->get_result();
        $student_answers = [];
        while ($sa = $sa_res->fetch_assoc()) {
            $student_answers[(int)$sa['question_id']] = $sa;
        }
        $sa_stmt->close();

        $drill_data = [
            'player'           => $drill_player_row,
            'questions'        => $correct_map,
            'student_answers'  => $student_answers,
        ];
    }
}

function qr_filters_qs(int $exam_id, string $view = 'summary', array $overrides = []): string {
    $base = array_merge([
        'exam_id'    => $exam_id,
        'view'       => $view,
        'session_id' => $_GET['session_id'] ?? '',
        'grade'      => $_GET['grade']      ?? '',
        'stream'     => $_GET['stream']     ?? '',
        'school'     => $_GET['school']     ?? '',
        'search'     => $_GET['search']     ?? '',
    ], $overrides);
    $base = array_filter($base, fn($v) => $v !== '' && $v !== null);
    return http_build_query($base);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Per-question report — <?= $exam_title ?></title>
<link rel="stylesheet" href="../dist/styles.css">
<link rel="stylesheet" href="<?= APP_BASE_URL ?>/exams/assets/exam-theme.css">
<style>
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
        padding: 10px 14px !important;
        vertical-align: middle;
    }
    .exam-glass tbody tr:hover { background: rgba(168,85,247,.08) !important; }
    .qcell-ok   { color:#86efac !important; font-weight:700; }
    .qcell-bad  { color:#fca5a5 !important; font-weight:700; }
    .qcell-skip { color:#94a3b8 !important; }
    .matrix-table th, .matrix-table td { padding: 8px 10px !important; text-align:center; }
    .matrix-table td.name { text-align:left; white-space:nowrap; }
    .tab-btn {
        padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600;
        background: rgba(255,255,255,.05); color:#cbd5e1; border:1px solid rgba(168,85,247,.3);
        text-decoration:none;
    }
    .tab-btn.active { background: linear-gradient(135deg,#7c3aed,#a855f7); color:#fff; border-color: transparent; }
    .pct-bar { width:80px; height:6px; background:rgba(255,255,255,.08); border-radius:99px; overflow:hidden; }
    .pct-bar > div { height:100%; border-radius:99px; }
</style>
</head>
<body class="bg-gray-100 min-h-screen exam-dark">
<?php include('../Auth/SF/header.php'); ?>

<div class="flex flex-1 overflow-hidden">

    <div class="bg-white border-r border-gray-200 min-h-screen w-64 hidden md:block">
        <?php include('./dynamic_sidebar.php'); ?>
    </div>

    <div class="flex-1 p-8 overflow-y-auto">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-xl font-semibold" style="color:#fff">Per-question report</h1>
                <p class="text-sm" style="color:#cbd5e1">What each student got right, wrong, or skipped</p>
            </div>
            <div class="flex gap-3">
                <a href="exam_report.php?exam_id=<?= $exam_id ?>"
                   class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg shadow">
                    ← Leaderboard
                </a>
                <a href="?<?= qr_filters_qs($exam_id, $view, ['format' => 'xls']) ?>"
                   class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg shadow">
                    📥 Download matrix (Excel)
                </a>
            </div>
        </div>

        <!-- Exam Switcher -->
        <div class="mb-6">
            <form method="GET" class="flex items-center gap-3">
                <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">
                <label class="text-sm" style="color:#cbd5e1">Viewing exam:</label>
                <select name="exam_id" onchange="this.form.submit()"
                        class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                    <?php while ($e = $all_exams->fetch_assoc()):
                        $is_mine = ((int)$e['created_by'] === (int)$acl['user_id']);
                        $tag = $is_mine ? '' : ' — by ' . trim($e['owner_name'] ?: 'colleague');
                    ?>
                        <option value="<?= $e['exam_id'] ?>" <?= $e['exam_id'] == $exam_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['title'] . $tag) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <!-- Info -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border exam-glass">
                <p class="text-xs text-gray-500 mb-1">Exam title</p>
                <p class="font-semibold text-gray-800"><?= $exam_title ?></p>
            </div>
            <div class="bg-white p-4 rounded-xl border exam-glass">
                <p class="text-xs text-gray-500 mb-1">Questions</p>
                <p class="font-semibold text-gray-800"><?= count($questions) ?></p>
            </div>
            <div class="bg-white p-4 rounded-xl border exam-glass">
                <p class="text-xs text-gray-500 mb-1">Students in view</p>
                <p class="font-semibold text-gray-800"><?= count($players) ?></p>
            </div>
            <div class="bg-white p-4 rounded-xl border exam-glass">
                <p class="text-xs text-gray-500 mb-1">Total marks</p>
                <p class="font-semibold text-blue-600"><?= $total_marks ?></p>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="flex flex-wrap gap-3 items-center mb-5">
            <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
            <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">

            <?php if (count($sessions) > 0): ?>
            <select name="session_id" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                <option value="0">All sessions</option>
                <?php foreach ($sessions as $s): ?>
                    <option value="<?= $s['session_id'] ?>" <?= $filter_session == $s['session_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['session_label']) ?> (<?= $s['session_code'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <select name="grade" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                <option value="">All grades</option>
                <?php foreach ($distinct_grades as $g): ?>
                    <option value="<?= htmlspecialchars($g['grade']) ?>" <?= $filter_grade === $g['grade'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['grade']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="stream" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                <option value="">All streams</option>
                <?php foreach ($distinct_streams as $s): ?>
                    <option value="<?= htmlspecialchars($s['stream']) ?>" <?= $filter_stream === $s['stream'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['stream']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="school" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">
                <option value="">All schools</option>
                <?php foreach ($distinct_schools as $s): ?>
                    <option value="<?= htmlspecialchars($s['school']) ?>" <?= $filter_school === $s['school'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['school']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="search" value="<?= htmlspecialchars($filter_search) ?>"
                   placeholder="Search player..."
                   class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white">

            <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded">Filter</button>
        </form>

        <!-- View tabs -->
        <div class="flex gap-2 mb-5">
            <a class="tab-btn <?= $view === 'summary' ? 'active' : '' ?>"
               href="?<?= qr_filters_qs($exam_id, 'summary') ?>">📊 Question summary</a>
            <a class="tab-btn <?= $view === 'matrix'  ? 'active' : '' ?>"
               href="?<?= qr_filters_qs($exam_id, 'matrix') ?>">🧮 Student × Question matrix</a>
            <?php if ($view === 'student' && $drill_data): ?>
                <span class="tab-btn active">👤 <?= htmlspecialchars($drill_data['player']['nickname']) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($view === 'summary'): ?>
            <!-- ============ SUMMARY ============ -->
            <div class="bg-white rounded-xl border exam-glass">
                <div class="flex justify-between items-center px-6 py-4 border-b">
                    <h2 class="font-semibold" style="color:#fff">Per-question performance</h2>
                    <span class="text-sm" style="color:#cbd5e1"><?= count($questions) ?> question(s)</span>
                </div>
                <?php if (count($questions) === 0): ?>
                    <div class="text-center py-12"><p class="text-gray-400">No questions in this exam.</p></div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase">
                                <th class="px-6 py-3">#</th>
                                <th class="px-6 py-3">Question</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Marks</th>
                                <th class="px-6 py-3">Attempted</th>
                                <th class="px-6 py-3">Correct</th>
                                <th class="px-6 py-3">Wrong</th>
                                <th class="px-6 py-3">% Correct</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $idx = 1;
                        foreach ($questions as $q):
                            $qid = (int)$q['question_id'];
                            $s = $q_stats[$qid];
                            $denom = $s['attempted'] ?: 0;
                            $pct = $denom > 0 ? round(($s['correct'] / $denom) * 100) : 0;
                            $bar_color = $pct >= 60 ? '#22c55e' : ($pct >= 30 ? '#f59e0b' : '#ef4444');
                            $preview = mb_substr(strip_tags($q['question_text']), 0, 110);
                        ?>
                            <tr>
                                <td class="px-6 py-3 font-semibold" style="color:#cbd5e1"><?= $idx++ ?></td>
                                <td class="px-6 py-3"><?= htmlspecialchars($preview) ?><?= mb_strlen($q['question_text']) > 110 ? '…' : '' ?></td>
                                <td class="px-6 py-3"><?= htmlspecialchars($q['question_type']) ?></td>
                                <td class="px-6 py-3"><?= (int)$q['marks'] ?></td>
                                <td class="px-6 py-3"><?= $s['attempted'] ?></td>
                                <td class="px-6 py-3 qcell-ok"><?= $s['correct'] ?></td>
                                <td class="px-6 py-3 qcell-bad"><?= $s['wrong'] ?></td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="pct-bar"><div style="width:<?= $pct ?>%;background:<?= $bar_color ?>"></div></div>
                                        <span><?= $pct ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        <?php elseif ($view === 'matrix'): ?>
            <!-- ============ MATRIX ============ -->
            <div class="bg-white rounded-xl border exam-glass">
                <div class="flex justify-between items-center px-6 py-4 border-b">
                    <h2 class="font-semibold" style="color:#fff">Student × Question matrix</h2>
                    <span class="text-sm" style="color:#cbd5e1"><?= count($players) ?> student(s) · <?= count($questions) ?> question(s)</span>
                </div>
                <?php if (empty($players) || empty($questions)): ?>
                    <div class="text-center py-12"><p class="text-gray-400">No data for the current filters.</p></div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs matrix-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th style="text-align:left">Student</th>
                                <th>Score</th>
                                <?php $idx = 1; foreach ($questions as $q): ?>
                                    <th title="<?= htmlspecialchars(mb_substr(strip_tags($q['question_text']),0,80)) ?>">
                                        Q<?= $idx++ ?>
                                    </th>
                                <?php endforeach; ?>
                                <th>%</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $rank = 1;
                        foreach ($players as $p):
                            $pid = (int)$p['player_id'];
                            $pct = $total_marks > 0 ? round(($p['score'] / $total_marks) * 100) : 0;
                        ?>
                            <tr>
                                <td><?= $rank++ ?></td>
                                <td class="name"><?= htmlspecialchars($p['nickname']) ?>
                                    <div style="color:#94a3b8;font-size:11px">
                                        <?= htmlspecialchars($p['grade']) ?>
                                        <?= $p['stream'] ? ' · ' . htmlspecialchars($p['stream']) : '' ?>
                                        <?= $p['school'] ? ' · ' . htmlspecialchars($p['school']) : '' ?>
                                    </div>
                                </td>
                                <td><?= (int)$p['score'] ?>/<?= $total_marks ?></td>
                                <?php foreach ($questions as $q):
                                    $a = $answers_by_player_q[$pid][(int)$q['question_id']] ?? null;
                                    if (!$a || ($a['chosen'] === '' && !$a['is_correct'] && $a['points'] === 0)) {
                                        echo '<td class="qcell-skip">—</td>';
                                    } elseif ($a['is_correct']) {
                                        echo '<td class="qcell-ok">✓</td>';
                                    } else {
                                        echo '<td class="qcell-bad">✗</td>';
                                    }
                                endforeach; ?>
                                <td><?= $pct ?>%</td>
                                <td>
                                    <a href="?<?= qr_filters_qs($exam_id, 'student', ['player_id' => $pid]) ?>"
                                       style="color:#93c5fd;text-decoration:underline">Open</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        <?php elseif ($view === 'student'): ?>
            <!-- ============ STUDENT DEEP DIVE ============ -->
            <?php if (!$drill_data): ?>
                <div class="bg-white p-6 rounded-xl border exam-glass">
                    <p style="color:#cbd5e1">Student not found in this exam.</p>
                </div>
            <?php else:
                $p = $drill_data['player'];
                $score_pct = $total_marks > 0 ? round(($p['score'] / $total_marks) * 100) : 0;
            ?>
                <div class="bg-white p-6 rounded-xl border exam-glass mb-5">
                    <h2 class="font-semibold" style="color:#fff;font-size:18px"><?= htmlspecialchars($p['nickname']) ?></h2>
                    <p style="color:#cbd5e1;font-size:13px">
                        <?= htmlspecialchars($p['grade']) ?>
                        <?= $p['stream'] ? ' · ' . htmlspecialchars($p['stream']) : '' ?>
                        <?= $p['school'] ? ' · ' . htmlspecialchars($p['school']) : '' ?>
                    </p>
                    <p class="mt-2" style="color:#fff">
                        Score: <strong style="color:#93c5fd"><?= (int)$p['score'] ?> / <?= $total_marks ?> (<?= $score_pct ?>%)</strong>
                    </p>
                </div>

                <div class="bg-white rounded-xl border exam-glass">
                    <div class="px-6 py-4 border-b"><h2 class="font-semibold" style="color:#fff">Answers</h2></div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase">
                                    <th class="px-6 py-3">#</th>
                                    <th class="px-6 py-3">Question</th>
                                    <th class="px-6 py-3">Correct answer</th>
                                    <th class="px-6 py-3">Their answer</th>
                                    <th class="px-6 py-3">Marks</th>
                                    <th class="px-6 py-3">Result</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $idx = 1;
                            foreach ($drill_data['questions'] as $qid => $q):
                                $a = $drill_data['student_answers'][$qid] ?? null;
                                $is_correct = $a && (int)$a['is_correct'] === 1;
                                $points = $a ? (int)$a['points_earned'] : 0;
                                $chosen = $a ? (string)$a['chosen_answer'] : '';
                            ?>
                                <tr>
                                    <td class="px-6 py-3" style="color:#cbd5e1"><?= $idx++ ?></td>
                                    <td class="px-6 py-3"><?= htmlspecialchars($q['question_text']) ?></td>
                                    <td class="px-6 py-3 qcell-ok"><?= htmlspecialchars((string)($q['correct_text'] ?? '')) ?></td>
                                    <td class="px-6 py-3 <?= $is_correct ? 'qcell-ok' : ($chosen ? 'qcell-bad' : 'qcell-skip') ?>">
                                        <?= $chosen !== '' ? htmlspecialchars($chosen) : '— skipped —' ?>
                                    </td>
                                    <td class="px-6 py-3"><?= $points ?> / <?= (int)$q['marks'] ?></td>
                                    <td class="px-6 py-3">
                                        <?php if ($is_correct): ?>
                                            <span class="qcell-ok">✓ Correct</span>
                                        <?php elseif ($chosen === ''): ?>
                                            <span class="qcell-skip">— Skipped</span>
                                        <?php elseif (in_array($q['question_type'], ['essay','practical'], true)): ?>
                                            <span class="qcell-skip">⏳ Manual</span>
                                        <?php else: ?>
                                            <span class="qcell-bad">✗ Wrong</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include('../Auth/SF/footer.php'); ?>
</body>
</html>
<?php // Connection closed by db_connection.php shutdown handler ?>
