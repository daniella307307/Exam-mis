<?php
// ✅ Session + DB first — absolutely no output before this
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db.php');

$exam_id = $_GET['exam_id'] ?? 0;

if (!$exam_id) {
    echo "Invalid Exam ID.";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id=? LIMIT 1");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo "Exam not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = $_POST['title'];
    $topic  = $_POST['topic'];
    $grade  = $_POST['grade'];
    $status = $_POST['status'];

    if ($status === 'active' && empty($exam['exam_code'])) {
        $exam_code = mt_rand(100000, 999999);
    } else {
        $exam_code = $exam['exam_code'];
    }

    $update = $conn->prepare("UPDATE exams SET title=?, topic=?, grade=?, status=?, exam_code=? WHERE exam_id=?");
    $update->bind_param("sssssi", $title, $topic, $grade, $status, $exam_code, $exam_id);
    $update->execute();

    // Re-fetch updated exam so the form shows fresh data
    $stmt2 = $conn->prepare("SELECT * FROM exams WHERE exam_id=? LIMIT 1");
    $stmt2->bind_param("i", $exam_id);
    $stmt2->execute();
    $exam = $stmt2->get_result()->fetch_assoc();

    $success = true;
}

$qstmt = $conn->prepare("SELECT * FROM questions WHERE exam_id=? ORDER BY created_at ASC");
$qstmt->bind_param("i", $exam_id);
$qstmt->execute();
$questions = $qstmt->get_result();

// Safe escaped values for the form
$exam_title  = htmlspecialchars($exam['title'],  ENT_QUOTES, 'UTF-8');
$exam_topic  = htmlspecialchars($exam['topic'],  ENT_QUOTES, 'UTF-8');
$exam_grade  = htmlspecialchars($exam['grade'],  ENT_QUOTES, 'UTF-8');
$exam_status = $exam['status'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Exam — <?= $exam_title ?></title>
<link rel="stylesheet" href="../dist/styles.css">
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: #f5f3ef;
        color: #2c2c2c;
        margin: 0;
    }

    .page-shell {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .layout-wrapper {
        display: flex;
        flex: 1;
        overflow: hidden;
    }

    .sidebar-col {
        width: 256px;
        flex-shrink: 0;
        background: #fff;
        border-right: 1px solid #e8e4de;
        min-height: 100vh;
    }

    @media (max-width: 768px) {
        .sidebar-col { display: none; }
    }

    .main-col {
        flex: 1;
        padding: 3rem 2.5rem;
        overflow-y: auto;
    }

    .max-content { max-width: 760px; }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2.5rem;
    }

    .page-title {
        font-family: 'Lora', serif;
        font-size: 1.75rem;
        font-weight: 600;
        color: #1a1a1a;
        line-height: 1.2;
    }

    .page-subtitle {
        font-size: 0.8rem;
        color: #9a8f82;
        margin-top: 0.25rem;
        font-weight: 400;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.82rem;
        font-weight: 500;
        color: #7c6f63;
        text-decoration: none;
        padding: 0.5rem 1rem;
        border: 1px solid #ddd9d1;
        border-radius: 999px;
        background: #fff;
        transition: background 0.2s, color 0.2s;
        white-space: nowrap;
    }

    .back-link:hover {
        background: #2c2c2c;
        color: #fff;
        border-color: #2c2c2c;
    }

    .card {
        background: #fff;
        border: 1px solid #e8e4de;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .card-title {
        font-family: 'Lora', serif;
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f0ece5;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .card-title .icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: #f5f0e8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .alert-success {
        background: #f0faf4;
        border: 1px solid #b6e8cb;
        color: #1f7a48;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        font-size: 0.83rem;
        font-weight: 500;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .field-group { margin-bottom: 1.25rem; }

    .field-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 600;
        color: #6b6157;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.45rem;
    }

    .field-input,
    .field-select {
        width: 100%;
        padding: 0.65rem 0.9rem;
        border: 1px solid #ddd9d1;
        border-radius: 10px;
        font-size: 0.9rem;
        font-family: 'DM Sans', sans-serif;
        color: #2c2c2c;
        background: #fdfcfa;
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
    }

    .field-input:focus,
    .field-select:focus {
        border-color: #c4a97d;
        box-shadow: 0 0 0 3px rgba(196,169,125,0.15);
        background: #fff;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #2c2c2c;
        color: #fff;
        border: none;
        padding: 0.65rem 1.4rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        transition: background 0.2s, transform 0.15s;
    }

    .btn-primary:hover {
        background: #111;
        transform: translateY(-1px);
    }

    .btn-green {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #1a6b3c;
        color: #fff;
        text-decoration: none;
        padding: 0.55rem 1.1rem;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 600;
        transition: background 0.2s, transform 0.15s;
    }

    .btn-green:hover {
        background: #145530;
        transform: translateY(-1px);
    }

    .questions-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
    }

    .empty-state {
        text-align: center;
        padding: 2.5rem 1rem;
        color: #a89e92;
        font-size: 0.88rem;
    }

    .empty-state .emoji {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
    }

    .question-item {
        border: 1px solid #ede9e2;
        border-radius: 12px;
        padding: 1rem 1.2rem;
        margin-bottom: 0.85rem;
        background: #fdfcfa;
        transition: border-color 0.2s, box-shadow 0.2s;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .question-item:hover {
        border-color: #c4a97d;
        box-shadow: 0 2px 8px rgba(196,169,125,0.12);
    }

    .question-number {
        width: 28px;
        height: 28px;
        background: #f5f0e8;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 700;
        color: #7c6344;
        flex-shrink: 0;
    }

    .question-body { flex: 1; min-width: 0; }

    .question-text {
        font-size: 0.88rem;
        font-weight: 500;
        color: #2c2c2c;
        margin-bottom: 0.3rem;
        line-height: 1.5;
    }

    .question-meta {
        display: flex;
        gap: 0.6rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .badge {
        font-size: 0.72rem;
        padding: 0.18rem 0.6rem;
        border-radius: 999px;
        font-weight: 500;
    }

    .badge-type  { background: #ede9e2; color: #6b6157; }
    .badge-marks { background: #f0faf4; color: #1f7a48; }

    .question-actions {
        display: flex;
        gap: 0.6rem;
        flex-shrink: 0;
    }

    .action-link {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.3rem 0.75rem;
        border-radius: 8px;
        text-decoration: none;
        transition: background 0.15s;
    }

    .action-edit   { color: #2563eb; background: #eff6ff; }
    .action-edit:hover   { background: #dbeafe; }
    .action-delete { color: #dc2626; background: #fef2f2; }
    .action-delete:hover { background: #fee2e2; }

    .divider {
        height: 1px;
        background: #f0ece5;
        margin: 1rem 0 1.25rem;
    }
</style>
</head>

<body>
<div class="page-shell">

    <?php include('../Auth/SF/header.php'); ?>

    <div class="layout-wrapper">

        <!-- SIDEBAR -->
        <div class="sidebar-col">
            <?php include('dynamic_sidebar.php'); ?>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-col">
            <div class="max-content">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <div class="page-title">Edit Exam</div>
                        <div class="page-subtitle">Update details &amp; manage questions</div>
                    </div>
                    <a href="exams_library.php" class="back-link">← Back</a>
                </div>

                <!-- Success Alert -->
                <?php if (!empty($success)): ?>
                <div class="alert-success">✓ Exam updated successfully</div>
                <?php endif; ?>

                <!-- Exam Details Card -->
                <div class="card">
                    <div class="card-title">
                        <span class="icon">📋</span>
                        Exam Details
                    </div>

                    <form method="POST" action="edit_exam.php?exam_id=<?= $exam_id ?>">
                        <div class="field-group">
                            <label class="field-label">Title</label>
                            <input type="text" name="title" value="<?= $exam_title ?>" class="field-input" required>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Topic</label>
                            <input type="text" name="topic" value="<?= $exam_topic ?>" class="field-input">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Grade</label>
                            <input type="text" name="grade" value="<?= $exam_grade ?>" class="field-input">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Status</label>
                            <select name="status" class="field-select">
                                <option value="draft"  <?= $exam_status === 'draft'  ? 'selected' : '' ?>>Draft</option>
                                <option value="active" <?= $exam_status === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="ended"  <?= $exam_status === 'ended'  ? 'selected' : '' ?>>Ended</option>
                            </select>
                        </div>

                        <div style="padding-top:0.5rem;">
                            <button type="submit" class="btn-primary">✓ Save Changes</button>
                        </div>
                    </form>
                </div>

                <!-- Questions Card -->
                <div class="card">
                    <div class="questions-header">
                        <div class="card-title" style="margin-bottom:0; border-bottom:none; padding-bottom:0;">
                            <span class="icon">❓</span>
                            Questions
                            <span style="font-size:0.75rem; color:#9a8f82; font-weight:400;">
                                (<?= $questions->num_rows ?>)
                            </span>
                        </div>
                        <a href="add_question.php?exam_id=<?= $exam_id ?>" class="btn-green">+ Add Question</a>
                    </div>

                    <div class="divider"></div>

                    <?php if ($questions->num_rows === 0): ?>
                        <div class="empty-state">
                            <span class="emoji">📝</span>
                            No questions yet — add your first one above.
                        </div>
                    <?php endif; ?>

                    <?php $i = 1; while ($q = $questions->fetch_assoc()): ?>
                    <div class="question-item">
                        <div class="question-number"><?= $i++ ?></div>

                        <div class="question-body">
                            <div class="question-text">
                                <?= htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div class="question-meta">
                                <span class="badge badge-type"><?= htmlspecialchars($q['question_type'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="badge badge-marks"><?= (int)$q['marks'] ?> mark<?= $q['marks'] != 1 ? 's' : '' ?></span>
                            </div>
                        </div>

                        <div class="question-actions">
                            <a href="edit_question.php?question_id=<?= (int)$q['question_id'] ?>" class="action-link action-edit">Edit</a>
                            <a href="delete_question.php?question_id=<?= (int)$q['question_id'] ?>&exam_id=<?= $exam_id ?>"
                               class="action-link action-delete"
                               onclick="return confirm('Delete this question?')">Delete</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

            </div>
        </div>
    </div>

    <?php include('../Auth/SF/footer.php'); ?>
</div>
</body>
</html>