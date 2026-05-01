<?php
require_once('../db_connection.php');

$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

if (!$exam_id) {
    // Show list of practical exams to choose from
    $exams_res = $conn->query("
        SELECT DISTINCT e.exam_id, e.title, e.topic, e.grade, e.created_at,
               COUNT(DISTINCT p.player_id) AS total_students
        FROM exams e
        JOIN questions q ON q.exam_id = e.exam_id AND q.question_type = 'practical'
        LEFT JOIN players p ON p.exam_id = e.exam_id
        GROUP BY e.exam_id
        ORDER BY e.created_at DESC
    ");
    $exams = $exams_res ? $exams_res->fetch_all(MYSQLI_ASSOC) : [];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assignment Submissions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Segoe UI',sans-serif;background:#f8fafc;color:#1e293b;padding:32px 20px}
    .container{max-width:900px;margin:0 auto}
    h1{font-size:24px;font-weight:800;margin-bottom:6px;color:#1e293b}
    .sub{color:#64748b;font-size:14px;margin-bottom:28px}
    .card{background:white;border:1px solid #e2e8f0;border-radius:12px;padding:20px 24px;
          display:flex;align-items:center;gap:16px;margin-bottom:12px;
          text-decoration:none;color:inherit;transition:box-shadow .2s,border-color .2s}
    .card:hover{box-shadow:0 4px 16px rgba(0,0,0,.08);border-color:#a855f7}
    .icon{width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#a855f7);
          display:flex;align-items:center;justify-content:center;color:white;font-size:20px;flex-shrink:0}
    .info{flex:1}
    .title{font-weight:700;font-size:16px;margin-bottom:2px}
    .meta{font-size:13px;color:#64748b}
    .badge{background:#ede9fe;color:#7c3aed;font-size:12px;font-weight:700;padding:4px 10px;border-radius:99px}
    .empty{text-align:center;padding:60px 20px;color:#94a3b8}
    </style>
        <link rel="stylesheet" href="/Exam-mis/exams/assets/exam-theme.css">
</head>
    <body class="exam-dark">
    <div class="container">
    <h1><i class="fas fa-folder-open" style="color:#a855f7"></i> Assignment Submissions</h1>
    <p class="sub">Select a practical assignment to view submitted project links</p>

    <?php if (empty($exams)): ?>
    <div class="empty">
        <i class="fas fa-inbox" style="font-size:48px;display:block;margin-bottom:16px"></i>
        No practical assignments found yet.<br>
        Create an exam with a PDF practical question first.
    </div>
    <?php else: ?>
    <?php foreach ($exams as $e): ?>
    <a class="card" href="assignment_submissions.php?exam_id=<?= $e['exam_id'] ?>">
        <div class="icon"><i class="fas fa-file-pdf"></i></div>
        <div class="info">
            <div class="title"><?= htmlspecialchars($e['title']) ?></div>
            <div class="meta"><?= htmlspecialchars($e['topic']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($e['grade']) ?> &nbsp;·&nbsp; <?= date('M j, Y', strtotime($e['created_at'])) ?></div>
        </div>
        <div class="badge"><?= $e['total_students'] ?> student<?= $e['total_students'] != 1 ? 's' : '' ?></div>
        <i class="fas fa-chevron-right" style="color:#cbd5e1"></i>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
    </div>
    </body>
    </html>
    <?php
    $conn->close();
    exit();
}

// ── Load exam + practical questions ──────────────────────────────
$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();
if (!$exam) { echo "Exam not found."; exit(); }

// Get practical questions with their PDF URLs
$pq_stmt = $conn->prepare("
    SELECT q.question_id, q.question_text, o.option_text AS pdf_url
    FROM questions q
    LEFT JOIN options o ON o.question_id = q.question_id
    WHERE q.exam_id = ? AND q.question_type = 'practical'
    LIMIT 1
");
$pq_stmt->bind_param("i", $exam_id);
$pq_stmt->execute();
$practical_q = $pq_stmt->get_result()->fetch_assoc();

// Get all students + their submitted project links
$sub_stmt = $conn->prepare("
    SELECT p.player_id, p.nickname, p.score,
           a.chosen_answer AS project_link,
           a.answer_id
    FROM players p
    LEFT JOIN answers a ON a.player_id = p.player_id
                       AND a.exam_id   = p.exam_id
                       AND a.question_id = ?
    WHERE p.exam_id = ?
    ORDER BY p.nickname ASC
");
$qid_for_sub = $practical_q['question_id'] ?? 0;
$sub_stmt->bind_param("ii", $qid_for_sub, $exam_id);
$sub_stmt->execute();
$submissions = $sub_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }

$submitted   = array_filter($submissions, fn($s) => !empty($s['project_link']));
$pending     = array_filter($submissions, fn($s) => empty($s['project_link']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Submissions – <?= htmlspecialchars($exam['title']) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',sans-serif;background:#f8fafc;color:#1e293b;padding:32px 20px}
.container{max-width:1000px;margin:0 auto}

.back{display:inline-flex;align-items:center;gap:8px;color:#7c3aed;font-weight:700;font-size:14px;
      text-decoration:none;margin-bottom:20px}
.back:hover{text-decoration:underline}

.page-header{margin-bottom:28px}
.page-header h1{font-size:22px;font-weight:800;margin-bottom:4px}
.page-header p{color:#64748b;font-size:14px}

.stats-row{display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap}
.stat{background:white;border:1px solid #e2e8f0;border-radius:10px;padding:14px 20px;text-align:center;flex:1;min-width:120px}
.stat-val{font-size:26px;font-weight:900}
.stat-lbl{font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-top:2px}
.stat.green .stat-val{color:#16a34a}
.stat.orange .stat-val{color:#ea580c}
.stat.purple .stat-val{color:#7c3aed}

.pdf-banner{background:linear-gradient(135deg,#1e1b4b,#312e81);border-radius:12px;padding:20px 24px;
            margin-bottom:24px;display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.pdf-banner .pdf-icon{font-size:36px}
.pdf-banner .pdf-info{flex:1}
.pdf-banner .pdf-title{color:white;font-weight:700;font-size:16px;margin-bottom:4px}
.pdf-banner .pdf-sub{color:#a5b4fc;font-size:13px}
.pdf-btn{padding:10px 18px;background:#a855f7;color:white;border-radius:8px;
         text-decoration:none;font-weight:700;font-size:13px;white-space:nowrap}
.pdf-btn:hover{background:#9333ea}

.section-title{font-size:13px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
               color:#64748b;margin-bottom:12px}

.submission-row{background:white;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;
                margin-bottom:8px;display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.submission-row.pending{opacity:.7}
.avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#a855f7);
        display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:14px;flex-shrink:0}
.student-name{font-weight:700;font-size:15px;flex:1;min-width:120px}
.link-area{flex:2;min-width:180px}
.project-link{display:inline-flex;align-items:center;gap:6px;color:#7c3aed;font-weight:700;
              font-size:13px;text-decoration:none;word-break:break-all}
.project-link:hover{text-decoration:underline}
.no-link{color:#94a3b8;font-size:13px;font-style:italic}
.status-badge{padding:4px 12px;border-radius:99px;font-size:12px;font-weight:700;white-space:nowrap}
.badge-done{background:#dcfce7;color:#16a34a}
.badge-pending{background:#fef3c7;color:#92400e}

.empty-state{text-align:center;padding:40px;color:#94a3b8}

@media(max-width:600px){
    .submission-row{flex-direction:column;align-items:flex-start}
    .stats-row .stat{flex:1 1 calc(50% - 6px)}
}
</style>
    <link rel="stylesheet" href="/Exam-mis/exams/assets/exam-theme.css">
</head>
<body class="exam-dark">
<div class="container">

<a href="assignment_submissions.php" class="back">
    <i class="fas fa-arrow-left"></i> All Assignments
</a>

<div class="page-header">
    <h1><i class="fas fa-tasks" style="color:#a855f7"></i> <?= htmlspecialchars($exam['title']) ?></h1>
    <p><?= htmlspecialchars($exam['topic']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($exam['grade']) ?> &nbsp;·&nbsp; Exam Code: <strong><?= htmlspecialchars($exam['exam_code']) ?></strong></p>
</div>

<!-- Stats -->
<div class="stats-row">
    <div class="stat purple">
        <div class="stat-val"><?= count($submissions) ?></div>
        <div class="stat-lbl">Total Students</div>
    </div>
    <div class="stat green">
        <div class="stat-val"><?= count($submitted) ?></div>
        <div class="stat-lbl">Submitted</div>
    </div>
    <div class="stat orange">
        <div class="stat-val"><?= count($pending) ?></div>
        <div class="stat-lbl">Pending</div>
    </div>
</div>

<!-- PDF Banner -->
<?php if ($practical_q && !empty($practical_q['pdf_url'])): ?>
<div class="pdf-banner">
    <div class="pdf-icon">📄</div>
    <div class="pdf-info">
        <div class="pdf-title">Assignment PDF</div>
        <div class="pdf-sub">The instructions document uploaded for this assignment</div>
    </div>
    <a href="<?= htmlspecialchars($practical_q['pdf_url']) ?>" target="_blank" class="pdf-btn">
        <i class="fas fa-external-link-alt"></i> Open PDF
    </a>
</div>
<?php endif; ?>

<!-- Submitted -->
<?php if (!empty($submitted)): ?>
<div class="section-title"><i class="fas fa-check-circle" style="color:#16a34a"></i> Submitted (<?= count($submitted) ?>)</div>

<?php foreach ($submitted as $s):
    $initials = strtoupper(substr($s['nickname'], 0, 2));
    $link = htmlspecialchars($s['project_link']);
    $short = strlen($s['project_link']) > 55 ? substr($s['project_link'], 0, 55) . '…' : $s['project_link'];
?>
<div class="submission-row">
    <div class="avatar"><?= $initials ?></div>
    <div class="student-name"><?= htmlspecialchars($s['nickname']) ?></div>
    <div class="link-area">
        <a href="<?= $link ?>" target="_blank" rel="noopener noreferrer" class="project-link">
            <i class="fas fa-external-link-alt"></i>
            <?= htmlspecialchars($short) ?>
        </a>
    </div>
    <span class="status-badge badge-done"><i class="fas fa-check"></i> Submitted</span>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- Pending -->
<?php if (!empty($pending)): ?>
<div class="section-title" style="margin-top:24px"><i class="fas fa-clock" style="color:#ea580c"></i> Not Submitted (<?= count($pending) ?>)</div>

<?php foreach ($pending as $s):
    $initials = strtoupper(substr($s['nickname'], 0, 2));
?>
<div class="submission-row pending">
    <div class="avatar" style="background:#94a3b8"><?= $initials ?></div>
    <div class="student-name"><?= htmlspecialchars($s['nickname']) ?></div>
    <div class="link-area"><span class="no-link">No link submitted yet</span></div>
    <span class="status-badge badge-pending"><i class="fas fa-hourglass-half"></i> Pending</span>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (empty($submissions)): ?>
<div class="empty-state">
    <i class="fas fa-users" style="font-size:40px;display:block;margin-bottom:12px"></i>
    No students have joined this exam yet.
</div>
<?php endif; ?>

</div>
</body>
</html>
