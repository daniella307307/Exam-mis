<?php
/**
 * Read-only preview of a Public-Library exam.
 *
 * Lets a teacher evaluate the exam *structure* — title, topic, questions,
 * marks, option text — without leaking the original author's answer keys.
 * MCQ options are listed but not marked correct/incorrect; short-answer's
 * expected answer is replaced with a "(revealed after republish)" hint.
 *
 * Eligible sources: is_public = 1 (the only way an exam reaches the Public
 * Library). Same-school colleagues' exams should not be previewed here —
 * they're already editable inline from "My Exams" under the widened ACL.
 *
 * From this page the teacher can click "Republish to my school" which POSTs
 * to clone_exam.php and lands them on the cloned copy in their dashboard.
 */
require_once('../db_connection.php');
require_once(__DIR__ . '/lib/exam_acl.php');

$acl = exam_acl_context($conn);
if (!$acl['user_id']) {
    header('Location: ' . APP_BASE_URL . '/index.php');
    exit;
}

$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
if ($exam_id <= 0) {
    echo '<p>No exam specified. <a href="exams_dashboard.php">Back to dashboard</a></p>';
    exit;
}

$stmt = $conn->prepare(
    "SELECT e.exam_id, e.title, e.topic, e.grade, e.duration, e.is_public,
            e.created_by, e.school_id, e.created_at,
            COALESCE(CONCAT(u.firstname,' ',u.lastname), '') AS owner_name,
            COALESCE(s.school_name, '') AS school_name
       FROM exams e
       LEFT JOIN users   u ON u.user_id   = e.created_by
       LEFT JOIN schools s ON s.school_id = e.school_id
      WHERE e.exam_id = ? LIMIT 1"
);
$stmt->bind_param('i', $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exam) {
    echo '<p>Exam not found. <a href="exams_dashboard.php">Back to dashboard</a></p>';
    exit;
}

if ((int)$exam['is_public'] !== 1 && !$acl['is_developer']) {
    http_response_code(403);
    echo '<!doctype html><meta charset="utf-8"><title>403</title>'
       . '<div style="font-family:sans-serif;max-width:560px;margin:80px auto;text-align:center">'
       . '<h1 style="color:#dc2626">403 &mdash; Not in the Public Library</h1>'
       . '<p style="color:#475569">This exam isn&rsquo;t shared publicly. Preview is only for exams the original teacher has marked Public.</p>'
       . '<p><a href="exams_dashboard.php" style="color:#2563eb">&larr; Back to dashboard</a></p></div>';
    exit;
}

// Load questions + options for the preview.
$qstmt = $conn->prepare(
    "SELECT question_id, question_text, question_type, marks
       FROM questions WHERE exam_id = ? ORDER BY question_id ASC"
);
$qstmt->bind_param('i', $exam_id);
$qstmt->execute();
$questions = $qstmt->get_result()->fetch_all(MYSQLI_ASSOC);
$qstmt->close();

$opts_by_q = [];
if (!empty($questions)) {
    $qids = array_map(fn($q) => (int)$q['question_id'], $questions);
    $in   = implode(',', array_fill(0, count($qids), '?'));
    $types = str_repeat('i', count($qids));
    $ostmt = $conn->prepare(
        "SELECT question_id, option_text
           FROM options WHERE question_id IN ($in) ORDER BY option_id ASC"
    );
    $ostmt->bind_param($types, ...$qids);
    $ostmt->execute();
    $ores = $ostmt->get_result();
    while ($r = $ores->fetch_assoc()) {
        $opts_by_q[(int)$r['question_id']][] = $r['option_text'];
    }
    $ostmt->close();
}

$total_marks = 0;
foreach ($questions as $q) { $total_marks += (int)$q['marks']; }

$creator = trim((string)($exam['owner_name'] ?? '')) ?: 'a teacher';
$school  = trim((string)($exam['school_name'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Preview: <?= htmlspecialchars($exam['title']) ?></title>
<link rel="stylesheet" href="../dist/styles.css">
<link rel="stylesheet" href="<?= APP_BASE_URL ?>/exams/assets/exam-theme.css">
<style>
    body { background:#0f0a1f; color:#f1f5f9; font-family:'Segoe UI',sans-serif; padding:24px; }
    .wrap { max-width:920px; margin:0 auto; }
    .crumbs { color:#a78bfa; font-weight:700; font-size:13px; margin-bottom:8px; text-transform:uppercase; letter-spacing:1px; }
    .crumbs a { color:#a78bfa; text-decoration:none; }
    .hero {
        background:rgba(255,255,255,.05);
        border:1px solid rgba(168,85,247,.3);
        border-radius:14px;
        padding:24px 28px;
        margin-bottom:22px;
        backdrop-filter:blur(20px);
    }
    .hero h1 { font-size:26px; margin:0 0 8px; color:#fff; }
    .meta-row { display:flex; flex-wrap:wrap; gap:14px; color:#cbd5e1; font-size:14px; margin-bottom:14px; }
    .meta-row strong { color:#fff; }
    .credit-chip {
        display:inline-flex; align-items:center; gap:6px;
        padding:6px 14px; border-radius:999px;
        background:rgba(124,58,237,.18); color:#c4b5fd;
        font-size:13px; font-weight:700;
        border:1px solid rgba(168,85,247,.35);
    }
    .republish-btn {
        display:inline-flex; align-items:center; gap:8px;
        padding:12px 22px; border-radius:10px; border:none;
        background:linear-gradient(135deg,#10b981,#22c55e); color:#fff;
        font-weight:800; font-size:14px; cursor:pointer;
        box-shadow:0 8px 22px rgba(16,185,129,.35);
        text-decoration:none;
    }
    .republish-btn:hover { transform:translateY(-1px); }
    .back-btn {
        display:inline-block;
        padding:10px 18px; border-radius:8px;
        background:rgba(255,255,255,.08); color:#fff;
        font-weight:700; text-decoration:none;
        border:1px solid rgba(168,85,247,.3);
    }
    .q-card {
        background:rgba(255,255,255,.04);
        border:1px solid rgba(168,85,247,.22);
        border-radius:12px;
        padding:18px 22px;
        margin-bottom:14px;
    }
    .q-head { display:flex; justify-content:space-between; gap:12px; align-items:start; margin-bottom:10px; }
    .q-num { color:#a78bfa; font-weight:800; font-size:13px; text-transform:uppercase; letter-spacing:1px; }
    .q-type { font-size:11px; padding:3px 10px; border-radius:999px; background:rgba(59,130,246,.18); color:#93c5fd; border:1px solid rgba(59,130,246,.35); text-transform:uppercase; font-weight:800; letter-spacing:.5px; }
    .q-marks { color:#cbd5e1; font-size:12px; }
    .q-text { font-size:15px; color:#f1f5f9; margin-bottom:12px; line-height:1.5; }
    .opt {
        padding:10px 14px;
        background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.08);
        border-radius:8px;
        margin:6px 0;
        color:#e2e8f0;
        font-size:14px;
    }
    .key-hidden {
        color:#fbbf24; font-style:italic; font-size:13px;
        background:rgba(251,191,36,.08);
        border:1px dashed rgba(251,191,36,.4);
        padding:8px 12px; border-radius:8px;
        margin-top:8px;
    }
    .keys-banner {
        background:rgba(251,191,36,.1);
        border:1px solid rgba(251,191,36,.35);
        color:#fde68a;
        padding:10px 16px; border-radius:10px;
        font-size:13px; font-weight:600;
        margin-bottom:20px;
    }
</style>
</head>
<body>
<div class="wrap">
    <div class="crumbs">
        <a href="exams_dashboard.php?view=public">← Public Library</a>
    </div>

    <div class="hero">
        <h1>📖 <?= htmlspecialchars($exam['title']) ?></h1>
        <div class="meta-row">
            <span>📝 Topic: <strong><?= htmlspecialchars($exam['topic']) ?></strong></span>
            <span>👥 Grade: <strong><?= htmlspecialchars($exam['grade']) ?></strong></span>
            <span>⏱ Duration: <strong><?= (int)$exam['duration'] ?> min</strong></span>
            <span>🎯 Total marks: <strong><?= $total_marks ?></strong></span>
            <span>❓ Questions: <strong><?= count($questions) ?></strong></span>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px;">
            <span class="credit-chip">🧑‍🏫 By: <?= htmlspecialchars($creator) ?><?= $school !== '' ? ' @ ' . htmlspecialchars($school) : '' ?></span>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <button type="button" class="republish-btn" onclick="republish(<?= (int)$exam_id ?>)">
                📥 Republish to my school
            </button>
            <a href="exams_dashboard.php?view=public" class="back-btn">Cancel</a>
        </div>
    </div>

    <div class="keys-banner">
        🔒 Answer keys are hidden in preview. You&rsquo;ll see them after you republish — you become the owner of your school&rsquo;s copy.
    </div>

    <?php if (empty($questions)): ?>
        <p style="color:#cbd5e1;text-align:center;padding:30px;">This exam has no questions yet.</p>
    <?php else: ?>
        <?php foreach ($questions as $i => $q):
            $qtype = (string)$q['question_type'];
            $opts  = $opts_by_q[(int)$q['question_id']] ?? [];
        ?>
            <div class="q-card">
                <div class="q-head">
                    <div>
                        <span class="q-num">Q<?= $i + 1 ?></span>
                        <span class="q-type"><?= htmlspecialchars($qtype) ?></span>
                    </div>
                    <span class="q-marks"><?= (int)$q['marks'] ?> mark<?= $q['marks'] == 1 ? '' : 's' ?></span>
                </div>
                <div class="q-text"><?= nl2br(htmlspecialchars($q['question_text'])) ?></div>

                <?php if ($qtype === 'mcq'): ?>
                    <?php foreach ($opts as $idx => $ot): ?>
                        <div class="opt">
                            <strong><?= chr(65 + $idx) ?>.</strong>
                            <?= htmlspecialchars($ot) ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="key-hidden">🔒 Correct option hidden — visible to you after republishing.</div>

                <?php elseif ($qtype === 'true_false'): ?>
                    <div class="opt"><strong>A.</strong> True</div>
                    <div class="opt"><strong>B.</strong> False</div>
                    <div class="key-hidden">🔒 Correct answer hidden — visible to you after republishing.</div>

                <?php elseif ($qtype === 'short_answer'): ?>
                    <div class="key-hidden">🔒 Expected answer hidden — visible to you after republishing.</div>

                <?php elseif ($qtype === 'practical'): ?>
                    <?php if (!empty($opts)): ?>
                        <div class="opt">📎 Reference material: <em>linked PDF (visible after republish)</em></div>
                    <?php endif; ?>

                <?php else: /* essay etc. */ ?>
                    <div class="key-hidden">📝 Open-ended / essay — graded manually by the teacher.</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
async function republish(examId) {
    if (!confirm('📥 Republish this exam to your school?\n\nA fresh copy will be added to your dashboard. Your students will get a brand-new leaderboard.')) return;
    try {
        const fd = new FormData();
        fd.append('exam_id', examId);
        const r = await fetch('clone_exam.php', { method: 'POST', body: fd });
        const j = await r.json();
        if (!j.success) throw new Error(j.error || 'clone failed');
        alert(`✅ Republished!\n\nNew exam code: ${j.new_exam_code}\nQuestions copied: ${j.questions}\n\nTaking you to your dashboard…`);
        window.location.href = 'exams_dashboard.php?view=mine';
    } catch (err) {
        alert('❌ Republish failed: ' + err.message);
    }
}
</script>
</body>
</html>
