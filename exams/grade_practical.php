<?php
require_once('../db_connection.php');
session_start();

// Handle AJAX grading submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'grade') {
    header('Content-Type: application/json');
    $answer_id  = (int)($_POST['answer_id'] ?? 0);
    $player_id  = (int)($_POST['player_id'] ?? 0);
    $exam_id    = (int)($_POST['exam_id'] ?? 0);
    $points     = (int)($_POST['points'] ?? 0);
    $max_points = (int)($_POST['max_points'] ?? 0);

    if (!$answer_id || !$player_id || !$exam_id) {
        echo json_encode(['success' => false, 'error' => 'Missing parameters']);
        exit;
    }

    // Clamp points to max
    $points = min($points, $max_points);

    // Update answer points
    $stmt = $conn->prepare("UPDATE answers SET points_earned = ?, is_correct = 1 WHERE answer_id = ?");
    $stmt->bind_param("ii", $points, $answer_id);
    $stmt->execute();

    // Recalculate total score for this player
    $score_stmt = $conn->prepare("SELECT SUM(points_earned) AS total FROM answers WHERE player_id = ? AND exam_id = ?");
    $score_stmt->bind_param("ii", $player_id, $exam_id);
    $score_stmt->execute();
    $new_score = (int)($score_stmt->get_result()->fetch_assoc()['total'] ?? 0);

    // Update player total score
    $upd = $conn->prepare("UPDATE players SET score = ? WHERE player_id = ? AND exam_id = ?");
    $upd->bind_param("iii", $new_score, $player_id, $exam_id);
    $upd->execute();

    // Group propagation: if the graded player is a group placeholder, mirror
    // the new score onto every teammate (same exam_id + group_nbr) so the
    // teacher's manual grade flows to the named members too — otherwise they
    // stay stuck on the auto-graded value while the placeholder jumps up.
    $gn_stmt = $conn->prepare("SELECT group_nbr FROM players WHERE player_id = ? LIMIT 1");
    $gn_stmt->bind_param("i", $player_id);
    $gn_stmt->execute();
    $gn_row    = $gn_stmt->get_result()->fetch_assoc();
    $gn_stmt->close();
    $group_nbr = (int)($gn_row['group_nbr'] ?? 0);
    if ($group_nbr > 0) {
        $g_upd = $conn->prepare("UPDATE players SET score = ? WHERE exam_id = ? AND group_nbr = ?");
        $g_upd->bind_param("iii", $new_score, $exam_id, $group_nbr);
        $g_upd->execute();
        $g_upd->close();
    }

    echo json_encode(['success' => true, 'new_score' => $new_score, 'points' => $points]);
    exit;
}

// GET - show grading page
$exam_id = (int)($_GET['exam_id'] ?? 0);
if (!$exam_id) {
    die('<p>No exam specified. <a href="exams_dashboard.php">Back to dashboard</a></p>');
}

// Fetch exam info
$estmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
$estmt->bind_param("i", $exam_id);
$estmt->execute();
$exam = $estmt->get_result()->fetch_assoc();
if (!$exam) die('Exam not found.');

// Fetch every question that needs (or may need) a human grader.
// We invert the filter rather than enumerate types: anything that is NOT
// a known auto-gradable type falls into manual grading. This catches
// legacy hyphenated 'short-answer', missing/NULL types, and any future
// type added without updating this page.
$qstmt = $conn->prepare(
    "SELECT * FROM questions
       WHERE exam_id = ?
         AND (
             question_type IS NULL
             OR LOWER(REPLACE(question_type, '-', '_')) NOT IN ('mcq', 'multiple', 'true_false')
         )
       ORDER BY
         FIELD(LOWER(REPLACE(question_type, '-', '_')), 'practical', 'short_answer', 'essay'),
         question_id"
);
$qstmt->bind_param("i", $exam_id);
$qstmt->execute();
$practical_questions = $qstmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ------------------------------------------------------------------
// Filters: narrow the answer list to a specific class/stream/school
// so a teacher with 60+ students doesn't have to scroll past graded
// students from other classes. All filters live in GET params so the
// URL is shareable ("send this link to Daniella to grade her stream").
// ------------------------------------------------------------------
$filter_grade  = trim($_GET['grade']  ?? '');
$filter_stream = trim($_GET['stream'] ?? '');
$filter_school = trim($_GET['school'] ?? '');
$filter_status = trim($_GET['status'] ?? '');   // '', 'pending', 'graded'
$filter_search = trim($_GET['search'] ?? '');

// Distinct dropdown values — pulled from the full participant set so
// the dropdowns always show every option, regardless of which filter
// is currently applied (you can switch from "Grade 4" to "Grade 5"
// without losing the dropdown content).
$g_stmt = $conn->prepare("SELECT DISTINCT grade FROM players WHERE exam_id = ? AND grade IS NOT NULL AND grade <> '' ORDER BY grade");
$g_stmt->bind_param("i", $exam_id);
$g_stmt->execute();
$distinct_grades = $g_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$g_stmt->close();

$s_stmt = $conn->prepare("SELECT DISTINCT stream FROM players WHERE exam_id = ? AND stream IS NOT NULL AND stream <> '' ORDER BY stream");
$s_stmt->bind_param("i", $exam_id);
$s_stmt->execute();
$distinct_streams = $s_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$s_stmt->close();

$sc_stmt = $conn->prepare("SELECT DISTINCT school FROM players WHERE exam_id = ? AND school IS NOT NULL AND school <> '' ORDER BY school");
$sc_stmt->bind_param("i", $exam_id);
$sc_stmt->execute();
$distinct_schools = $sc_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$sc_stmt->close();

// Build the answer query dynamically. We always join players (we need
// nickname/grade/stream/school for display anyway), and we add WHERE
// fragments for whichever filters are active.
function build_answer_query(mysqli $conn, int $exam_id, int $qid,
                            string $g, string $s, string $sc,
                            string $status, string $search): array {
    $sql = "SELECT a.*, p.nickname, p.grade, p.stream, p.school
              FROM answers a
              JOIN players p ON a.player_id = p.player_id
             WHERE a.question_id = ? AND a.exam_id = ?";
    $types  = "ii";
    $params = [$qid, $exam_id];

    if ($g !== '')  { $sql .= " AND p.grade = ?";  $types .= "s"; $params[] = $g; }
    if ($s !== '')  { $sql .= " AND p.stream = ?"; $types .= "s"; $params[] = $s; }
    if ($sc !== '') { $sql .= " AND p.school = ?"; $types .= "s"; $params[] = $sc; }
    if ($search !== '') {
        $sql   .= " AND p.nickname LIKE ?";
        $types .= "s";
        $params[] = '%' . $search . '%';
    }
    if ($status === 'pending') {
        $sql .= " AND COALESCE(a.points_earned, 0) = 0";
    } elseif ($status === 'graded') {
        $sql .= " AND COALESCE(a.points_earned, 0) > 0";
    }
    $sql .= " ORDER BY p.grade, p.stream, p.nickname ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// Fetch the (filtered) answer set per open-ended question.
$data = [];
$visible_total = 0;
foreach ($practical_questions as $q) {
    $answers = build_answer_query($conn, $exam_id, (int)$q['question_id'],
        $filter_grade, $filter_stream, $filter_school, $filter_status, $filter_search);
    $data[] = ['question' => $q, 'answers' => $answers];
    $visible_total += count($answers);
}

$has_filter = ($filter_grade || $filter_stream || $filter_school || $filter_status || $filter_search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Grade Open-Ended Answers - <?= htmlspecialchars($exam['title']) ?></title>
<link rel="stylesheet" href="../dist/styles.css">
<style>
    .grade-card { background:rgba(255,255,255,.05); border-radius:14px; border:1px solid rgba(168,85,247,.3); padding:20px; margin-bottom:16px; backdrop-filter:blur(16px); color:#f1f5f9; }
    .student-name { font-size:16px; font-weight:800; color:#ffffff; }
    .answer-box { background:rgba(15,15,40,.55); border:1px solid rgba(168,85,247,.25); border-radius:8px; padding:12px; margin:10px 0; font-size:14px; word-break:break-all; color:#e2e8f0; }
    .answer-link { color:#c084fc; text-decoration:underline; font-weight:700; }
    .grade-input { display:flex; align-items:center; gap:10px; margin-top:12px; flex-wrap:wrap; color:#cbd5e1; }
    .grade-input label { color:#cbd5e1; font-weight:700; font-size:13px; }
    .grade-input input { width:90px; padding:9px; background:rgba(15,15,40,.6); border:1.5px solid rgba(168,85,247,.3); border-radius:8px; font-size:16px; font-weight:800; text-align:center; color:#ffffff; }
    .grade-input input:focus { border-color:#a855f7; background:rgba(15,15,40,.85); outline:none; box-shadow:0 0 0 3px rgba(168,85,247,.18); }
    .save-btn { padding:9px 22px; background:linear-gradient(135deg,#7c3aed,#a855f7); color:#fff !important; border:none; border-radius:8px; font-weight:800; cursor:pointer; font-size:14px; box-shadow:0 6px 18px rgba(124,58,237,.4); }
    .save-btn:hover { transform:translateY(-1px); box-shadow:0 10px 24px rgba(124,58,237,.5); }
    .saved-badge { display:none; background:rgba(34,197,94,.18); color:#86efac; padding:6px 12px; border-radius:20px; font-size:13px; font-weight:700; border:1px solid rgba(34,197,94,.4); }
    .points-display { font-size:13px; color:#cbd5e1; font-weight:700; }
    .empty-answer { color:#fca5a5; font-style:italic; font-weight:700; }
    .graded { border-left:4px solid #22c55e; }
    .ungraded { border-left:4px solid #f59e0b; }
    .stat-bar { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:24px; }
    .stat { background:rgba(255,255,255,.05); border-radius:12px; padding:16px; text-align:center; border:1px solid rgba(168,85,247,.3); }
    .stat-val { font-size:28px; font-weight:900; color:#facc15; }
    .stat-lbl { font-size:12px; color:#cbd5e1; margin-top:4px; text-transform:uppercase; letter-spacing:1px; }
    .pending-pill { display:inline-block; padding:3px 10px; border-radius:99px; background:rgba(250,204,21,.18); color:#fde68a; font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase; border:1px solid rgba(250,204,21,.4); }
    .graded-pill { display:inline-block; padding:3px 10px; border-radius:99px; background:rgba(34,197,94,.18); color:#86efac; font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase; border:1px solid rgba(34,197,94,.4); }
</style>
    <link rel="stylesheet" href="<?= APP_BASE_URL ?>/exams/assets/exam-theme.css">
</head>
<body class="bg-gray-100 min-h-screen exam-dark">
<?php include('../Auth/SF/header.php'); ?>

<div class="flex flex-1 overflow-hidden">
    <div class="bg-white border-r border-gray-200 min-h-screen w-64 hidden md:block">
        <?php include('./dynamic_sidebar.php'); ?>
    </div>

    <div class="flex-1 p-8 overflow-y-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-xl font-semibold" style="color:#fff">
                    🎓 Grade Open-Ended Answers
                </h1>
                <p class="text-sm" style="color:#cbd5e1">
                    Practical, short-answer & essay submissions for
                    <strong><?= htmlspecialchars($exam['title']) ?></strong>
                    &nbsp;·&nbsp; Grade: <?= htmlspecialchars($exam['grade']) ?>
                </p>
            </div>
            <a href="exam_report.php?exam_id=<?= $exam_id ?>"
               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm rounded-lg">
                ← Back to Report
            </a>
        </div>

        <!-- Filter bar: narrow the grading queue to a specific class/stream so
             a teacher with 60+ students doesn't have to scroll past graded
             ones from other classes. -->
        <?php if (!empty($practical_questions)): ?>
        <form method="GET" class="grade-filters" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:20px;background:rgba(255,255,255,.05);border:1px solid rgba(168,85,247,.3);border-radius:12px;padding:14px 18px;">
            <input type="hidden" name="exam_id" value="<?= (int)$exam_id ?>">

            <span style="color:#cbd5e1;font-size:13px;font-weight:700;letter-spacing:.5px;">FILTER:</span>

            <select name="grade" style="padding:7px 10px;border-radius:8px;border:1.5px solid rgba(168,85,247,.3);background:rgba(15,15,40,.6);color:#f1f5f9;font-weight:700;font-size:13px;">
                <option value="">All grades</option>
                <?php foreach ($distinct_grades as $g): ?>
                    <option value="<?= htmlspecialchars($g['grade']) ?>" <?= $filter_grade === $g['grade'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['grade']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="stream" style="padding:7px 10px;border-radius:8px;border:1.5px solid rgba(168,85,247,.3);background:rgba(15,15,40,.6);color:#f1f5f9;font-weight:700;font-size:13px;">
                <option value="">All streams</option>
                <?php foreach ($distinct_streams as $s): ?>
                    <option value="<?= htmlspecialchars($s['stream']) ?>" <?= $filter_stream === $s['stream'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['stream']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (count($distinct_schools) > 1): ?>
            <select name="school" style="padding:7px 10px;border-radius:8px;border:1.5px solid rgba(168,85,247,.3);background:rgba(15,15,40,.6);color:#f1f5f9;font-weight:700;font-size:13px;">
                <option value="">All schools</option>
                <?php foreach ($distinct_schools as $sc): ?>
                    <option value="<?= htmlspecialchars($sc['school']) ?>" <?= $filter_school === $sc['school'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sc['school']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <select name="status" style="padding:7px 10px;border-radius:8px;border:1.5px solid rgba(168,85,247,.3);background:rgba(15,15,40,.6);color:#f1f5f9;font-weight:700;font-size:13px;">
                <option value=""        <?= $filter_status === ''        ? 'selected' : '' ?>>All status</option>
                <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>⏳ Needs grading</option>
                <option value="graded"  <?= $filter_status === 'graded'  ? 'selected' : '' ?>>✓ Already graded</option>
            </select>

            <input type="text" name="search" value="<?= htmlspecialchars($filter_search) ?>"
                   placeholder="Search by name…"
                   style="padding:7px 12px;border-radius:8px;border:1.5px solid rgba(168,85,247,.3);background:rgba(15,15,40,.6);color:#f1f5f9;font-weight:600;font-size:13px;min-width:200px;">

            <button type="submit" style="padding:7px 18px;border-radius:8px;border:none;background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;font-weight:800;font-size:13px;cursor:pointer;">Apply</button>

            <?php if ($has_filter): ?>
                <a href="?exam_id=<?= (int)$exam_id ?>" style="color:#fca5a5;font-size:13px;font-weight:700;text-decoration:underline;margin-left:6px;">Clear</a>
            <?php endif; ?>

            <span style="margin-left:auto;color:#cbd5e1;font-size:13px;font-weight:700;">
                <?php if ($has_filter): ?>
                    Showing <strong style="color:#fff"><?= (int)$visible_total ?></strong> matching answer<?= $visible_total === 1 ? '' : 's' ?>
                <?php else: ?>
                    <strong style="color:#fff"><?= (int)$visible_total ?></strong> answer<?= $visible_total === 1 ? '' : 's' ?> across <?= count($practical_questions) ?> question<?= count($practical_questions) === 1 ? '' : 's' ?>
                <?php endif; ?>
            </span>
        </form>
        <?php endif; ?>

        <?php if (empty($practical_questions)): ?>
            <div class="bg-white rounded-xl p-8 text-center text-gray-400">
                <p class="text-lg">No open-ended questions (practical, short-answer or essay) found for this exam.</p>
            </div>
        <?php else: ?>
            <?php foreach ($data as $section):
                $q = $section['question'];
                $answers = $section['answers'];
                $total_answers = count($answers);
                $graded = array_filter($answers, fn($a) => $a['points_earned'] > 0);
                $ungraded = $total_answers - count($graded);
            ?>
                <!-- Question Section -->
                <div class="bg-white rounded-xl border p-6 mb-6">
                    <h2 class="font-bold mb-2" style="color:#fff">
                        📋 <?= htmlspecialchars($q['question_text']) ?>
                    </h2>
                    <p class="text-sm mb-4" style="color:#cbd5e1">
                        Max marks: <strong style="color:#fff"><?= $q['marks'] ?></strong>
                    </p>

                    <!-- Stats -->
                    <div class="stat-bar">
                        <div class="stat">
                            <div class="stat-val"><?= $total_answers ?></div>
                            <div class="stat-lbl">Submissions</div>
                        </div>
                        <div class="stat">
                            <div class="stat-val" style="color:#22c55e"><?= count($graded) ?></div>
                            <div class="stat-lbl">Graded</div>
                        </div>
                        <div class="stat">
                            <div class="stat-val" style="color:#f59e0b"><?= $ungraded ?></div>
                            <div class="stat-lbl">Pending</div>
                        </div>
                    </div>

                    <!-- Student Answers -->
                    <?php if (empty($answers)): ?>
                        <p class="text-gray-400 text-center py-4">
                            <?= $has_filter
                                ? 'No submissions match the current filters for this question.'
                                : 'No submissions yet.' ?>
                        </p>
                    <?php else: ?>
                        <?php foreach ($answers as $a):
                            $is_graded = $a['points_earned'] > 0;
                            $card_class = $is_graded ? 'graded' : 'ungraded';
                            $answer = trim($a['chosen_answer']);
                            $is_url = filter_var($answer, FILTER_VALIDATE_URL);
                        ?>
                        <div class="grade-card <?= $card_class ?>" id="card-<?= $a['answer_id'] ?>">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="student-name">
                                        👤 <?= htmlspecialchars($a['nickname']) ?>
                                    </div>
                                    <div class="text-xs mt-1" style="color:#cbd5e1;">
                                        <?php
                                            $bits = [];
                                            if (!empty($a['grade']))  $bits[] = htmlspecialchars($a['grade']);
                                            if (!empty($a['stream'])) $bits[] = 'Stream ' . htmlspecialchars($a['stream']);
                                            if (!empty($a['school'])) $bits[] = htmlspecialchars($a['school']);
                                            echo implode(' · ', $bits);
                                        ?>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="<?= $is_graded ? 'graded-pill' : 'pending-pill' ?>">
                                        <?= $is_graded ? '✅ Graded' : '⏳ Pending' ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Student's Answer -->
                            <div class="answer-box">
                                <?php if (empty($answer)): ?>
                                    <span class="empty-answer">⚠️ No answer submitted</span>
                                <?php elseif ($is_url): ?>
                                    <a href="<?= htmlspecialchars($answer) ?>" 
                                       target="_blank" class="answer-link">
                                        🔗 <?= htmlspecialchars($answer) ?>
                                    </a>
                                <?php else: ?>
                                    <?= nl2br(htmlspecialchars($answer)) ?>
                                <?php endif; ?>
                            </div>

                            <!-- Grading Input -->
                            <div class="grade-input">
                                <label>Score:</label>
                                <input 
                                    type="number" 
                                    id="pts-<?= $a['answer_id'] ?>"
                                    value="<?= $a['points_earned'] ?>" 
                                    min="0" 
                                    max="<?= $q['marks'] ?>"
                                    placeholder="0"
                                >
                                <span class="points-display">/ <?= $q['marks'] ?> marks</span>
                                <button class="save-btn" 
                                    onclick="saveGrade(
                                        <?= $a['answer_id'] ?>, 
                                        <?= $a['player_id'] ?>, 
                                        <?= $exam_id ?>, 
                                        <?= $q['marks'] ?>
                                    )">
                                    💾 Save
                                </button>
                                <span class="saved-badge" id="badge-<?= $a['answer_id'] ?>">
                                    ✅ Saved!
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include('../Auth/SF/footer.php'); ?>

<script>
async function saveGrade(answerId, playerId, examId, maxPoints) {
    const pts = parseInt(document.getElementById('pts-' + answerId).value) || 0;

    if (pts < 0 || pts > maxPoints) {
        alert(`Score must be between 0 and ${maxPoints}`);
        return;
    }

    try {
        const res = await fetch('grade_practical.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=grade&answer_id=${answerId}&player_id=${playerId}&exam_id=${examId}&points=${pts}&max_points=${maxPoints}`
        });

        const data = await res.json();

        if (data.success) {
            // Show saved badge
            const badge = document.getElementById('badge-' + answerId);
            badge.style.display = 'inline-block';
            badge.textContent = `✅ Saved! (${data.points} pts)`;
            setTimeout(() => badge.style.display = 'none', 3000);

            // Update card style to graded
            const card = document.getElementById('card-' + answerId);
            card.classList.remove('ungraded');
            card.classList.add('graded');
        } else {
            alert('Error saving grade: ' + data.error);
        }
    } catch (e) {
        alert('Error: ' + e.message);
    }
}
</script>
</body>
</html>
