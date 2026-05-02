<?php
require_once('../db_connection.php');

$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
if (!$exam_id) {
    echo "<p>No exam specified. <a href=\"exams_dashboard.php\">Back to dashboard</a></p>";
    exit;
}

// Fetch exam
$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ? LIMIT 1");
$stmt->bind_param('i', $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exam) {
    echo "<p>Exam not found. <a href=\"exams_dashboard.php\">Back to dashboard</a></p>";
    exit;
}

$exam_title = htmlspecialchars($exam['title']);
$exam_topic = htmlspecialchars($exam['topic']);
$exam_grade = htmlspecialchars($exam['grade']);
$exam_duration = htmlspecialchars($exam['duration']);
$exam_code = htmlspecialchars($exam['exam_code']);

// Fetch questions
$qstmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY question_id ASC");
$qstmt->bind_param('i', $exam_id);
$qstmt->execute();
$questions = $qstmt->get_result()->fetch_all(MYSQLI_ASSOC);
$qstmt->close();

// For each question fetch options
$options_map = [];
if (count($questions) > 0) {
    $ids = array_column($questions, 'question_id');
    // Prepare a single query to fetch options for all question ids
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    // Build types string
    $types = str_repeat('i', count($ids));
    $sql = "SELECT * FROM options WHERE question_id IN ($placeholders) ORDER BY option_id ASC";
    $stmt = $conn->prepare($sql);
    // bind params dynamically
    $refs = [];
    $stmt_params = [];
    $stmt_params[] = & $types; // not used by mysqli with dynamic bind, will use call_user_func_array
    // mysqli requires call_user_func_array on bind_param
    // Workaround: build query with integer values directly (safe because ids are ints from DB)
    $sql2 = "SELECT * FROM options WHERE question_id IN (" . implode(',', $ids) . ") ORDER BY option_id ASC";
    $res = $conn->query($sql2);
    while ($row = $res->fetch_assoc()) {
        $qid = $row['question_id'];
        if (!isset($options_map[$qid])) $options_map[$qid] = [];
        $options_map[$qid][] = $row;
    }
}

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>View Exam - <?= $exam_title ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body { font-family: Arial, Helvetica, sans-serif; padding:30px; }
    .card { max-width:900px; margin: 0 auto; border-radius:12px; padding:24px; }
    h1 { margin:0 0 6px 0; font-size:22px; color:#f8fafc; }
    .meta { color:#cbd5e1; margin-bottom:16px; }
    .exam-info { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
    .pill { background:rgba(168,85,247,.18); padding:8px 12px; border-radius:999px; font-weight:600; color:#f1f5f9; border:1px solid rgba(168,85,247,.3); }
    .questions { margin-top:18px; }
    .question { border-left:4px solid #a855f7; padding:14px 18px; margin-bottom:12px; border-radius:6px; background:rgba(255,255,255,.04); color:#f1f5f9; }
    .question h3 { margin:0 0 6px 0; font-size:16px; color:#f8fafc; }
    .opts { margin-top:8px; }
    .opt { padding:8px 12px; border-radius:6px; margin-bottom:6px; background:rgba(15,15,40,.55); border:1px solid rgba(168,85,247,.2); color:#f1f5f9; display:flex; justify-content:space-between; align-items:center; }
    .opt.correct { border-color:#22c55e; background:rgba(34,197,94,.12); color:#86efac; }
    .back { display:inline-block; margin-top:18px; text-decoration:none; color:#c084fc; font-weight:600; }
    .actions-top { display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
    .btn { display:inline-block; padding:10px 16px; border-radius:6px; text-decoration:none; font-weight:600; border:none; cursor:pointer; }
    .btn-edit { background:#28a745; color:white; }
    .btn-edit:hover { background:#218838; }
    .btn-delete { background:#dc3545; color:white; }
    .btn-delete:hover { background:#c82333; }
    .btn-back { background:#667eea; color:white; }
    .btn-back:hover { background:#764ba2; }
</style>
    <link rel="stylesheet" href="<?= APP_BASE_URL ?>/exams/assets/exam-theme.css">
</head>
<body class="exam-dark">
    <?php include('../Auth/SF/header.php'); ?>
<div class="flex">
    <div class="bg-white border-r border-gray-200 min-h-screen w-64 hidden md:block">
        <?php include('./dynamic_sidebar.php'); ?>
    </div>
    <div class="flex-1 p-6 overflow-y-auto">
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:20px;">
        <div>
            <h1><?= $exam_title ?></h1>
            <div class="meta">Topic: <strong><?= $exam_topic ?></strong> · Grade: <strong><?= $exam_grade ?></strong></div>
        </div>
        <div style="text-align:right;">
            <div class="pill">Exam Code: <?= $exam_code ?></div>
            <div style="margin-top:8px; font-size:13px; color:#666;">Duration: <?= $exam_duration ?> minutes</div>
        </div>
    </div>

    <div class="actions-top">
        <a href="exam_creator_working.php?exam_id=<?= $exam_id ?>" class="btn btn-edit">✏️ Edit Exam</a>
        <button class="btn btn-delete" onclick="deleteExam(<?= $exam_id ?>)">🗑️ Delete Exam</button>
        <a href="exams_dashboard.php" class="btn btn-back">← Back to Dashboard</a>
    </div>

    <div class="questions">
        <?php if (count($questions) === 0): ?>
            <p>No questions available for this exam.</p>
        <?php else: ?>
            <?php foreach ($questions as $i => $q):
                $qid = $q['question_id'];
                $qtext = htmlspecialchars($q['question_text']);
                $qtype = htmlspecialchars($q['question_type']);
                $marks = htmlspecialchars($q['marks']);
                $opts = $options_map[$qid] ?? [];
            ?>
                <div class="question">
                    <h3>Q<?= $i+1 ?>. <?= $qtext ?> <span style="font-weight:600; color:#333;"> (<?= $marks ?> pts)</span></h3>
                    <div style="color:#555; font-size:13px; margin-bottom:8px;">Type: <?= ucfirst(str_replace('_',' ', $qtype)) ?></div>
                    
                    <?php if ($qtype === 'practical'): ?>
                        <!-- PRACTICAL EXAM - DISPLAY PDF -->
                        <div style="background:#f0f7ff; border:2px solid #667eea; border-radius:8px; padding:16px; margin-top:12px;">
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                                <i class="fas fa-file-pdf" style="font-size:24px; color:#dc3545;"></i>
                                <span style="font-weight:600; color:#333;">Practical Exam PDF</span>
                            </div>
                            
                            <?php if (count($opts) > 0): 
                                $pdf_url = htmlspecialchars($opts[0]['option_text']);
                            ?>
                                <div style="margin-bottom:12px;">
                                    <a href="<?= $pdf_url ?>" target="_blank" style="display:inline-block; padding:10px 16px; background:#667eea; color:white; text-decoration:none; border-radius:6px; font-weight:600;">
                                        <i class="fas fa-download"></i> Download PDF
                                    </a>
                                </div>
                                
                                <!-- PDF VIEWER EMBED -->
                                <div style="border:1px solid #ddd; border-radius:6px; overflow:hidden; background:white;">
                                    <iframe 
                                      src="<?= APP_BASE_URL ?>/pdfjs/web/viewer.html?file=<?= urlencode($pdf_url) ?>"
                                      style="width:100%; height:600px; border:none;"
                                      allowfullscreen>
                                    </iframe>   
                                </div>
                                <div style="margin-top:8px; font-size:12px; color:#666;">
                                    <i class="fas fa-info-circle"></i> PDF viewer powered by Mozilla PDF.js. 
                                    <a href="<?= $pdf_url ?>" target="_blank" style="color:#667eea;">Open in new tab</a> for full screen
                                </div>
                            <?php else: ?>
                                <div style="color:#dc3545; font-weight:600;">⚠️ PDF file not found for this practical exam</div>
                            <?php endif; ?>
                        </div>
                    
                    <?php elseif ($qtype === 'mcq' || $qtype === 'multiple'): ?>
                        <div class="opts">
                            <?php foreach ($opts as $opt):
                                $is = $opt['is_correct'] ? 'correct' : '';
                                $text = htmlspecialchars($opt['option_text']);
                            ?>
                                <div class="opt <?= $is ?>">
                                    <div><strong><?= htmlspecialchars($opt['option_id']) ?>.</strong> <?= $text ?></div>
                                    <?php if ($opt['is_correct']): ?>
                                        <div style="color:#28a745; font-weight:700;">Correct</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($qtype === 'true_false' || $qtype === 'true-false' || $qtype === 'truefalse'): ?>
                        <div class="opts">
                            <?php
                                // Find which option is marked correct
                                foreach ($opts as $opt) {
                                    $text = htmlspecialchars($opt['option_text']);
                                    $is = $opt['is_correct'] ? 'correct' : '';
                                    echo "<div class=\"opt $is\">$text" . ($opt['is_correct'] ? ' <strong style=\"color:#28a745\">(Correct)</strong>' : '') . "</div>";
                                }
                            ?>
                        </div>
                    <?php else: ?>
                        <div style="padding:10px 12px; background:#fff; border:1px dashed #e6e6e6; border-radius:6px;">
                            <em>Short answer / Essay question — answer expected: (instructor notes)</em>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <a class="back" href="exams_dashboard.php">← Back to dashboard</a>
</div>

<script>
function deleteExam(examId) {
    if (!confirm('Are you sure you want to delete this exam and all its questions? This cannot be undone.')) {
        return;
    }
    
    fetch('delete_exam.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ exam_id: examId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Exam deleted successfully!');
            window.location.href = 'exams_dashboard.php';
        } else {
            alert('Error deleting exam: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(e => alert('Error: ' + e.message));
}
</script>
</div>
</div>
</body>
</html>
