<?php
session_start();
include("../db.php");

$cert_code = trim($_GET['code'] ?? '');
$cert = null;

if ($cert_code) {
    $stmt = $conn->prepare("
        SELECT sc.*, c.certification_name AS cert_level_name
        FROM student_certificates sc
        LEFT JOIN certifications c ON sc.certification_id = c.certification_id
        WHERE sc.cert_code = ?
    ");
    $stmt->bind_param("s", $cert_code);
    $stmt->execute();
    $cert = $stmt->get_result()->fetch_assoc();
} elseif (isset($_SESSION['exam_id'], $_SESSION['player_id'])) {
    $eid = (int)$_SESSION['exam_id'];
    $pid = (int)$_SESSION['player_id'];
    $stmt = $conn->prepare("
        SELECT sc.*, c.certification_name AS cert_level_name
        FROM student_certificates sc
        LEFT JOIN certifications c ON sc.certification_id = c.certification_id
        WHERE sc.player_id = ? AND sc.exam_id = ?
    ");
    $stmt->bind_param("ii", $pid, $eid);
    $stmt->execute();
    $cert = $stmt->get_result()->fetch_assoc();
}

if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }

if (!$cert) {
    ?><!DOCTYPE html>
    <html lang="en"><head><meta charset="UTF-8"><title>Certificate Not Found</title>
    <style>body{font-family:sans-serif;text-align:center;padding:60px;background:#f1f5f9;color:#334155}
    .box{background:white;border-radius:12px;padding:40px;display:inline-block;box-shadow:0 4px 20px rgba(0,0,0,.1)}
    a{color:#7c3aed;font-weight:700}</style>    <link rel="stylesheet" href="/Exam-mis/exams/assets/exam-theme.css">
</head>
    <body class="exam-dark"><div class="box">
    <h2>Certificate Not Found</h2>
    <p style="margin:16px 0;color:#64748b">You haven't earned a certificate for this exam yet, or the code is invalid.</p>
    <a href="join_exam.php">Back to Exams</a>
    </div></body></html>
    <?php
    exit();
}

$issued_date = date('F j, Y', strtotime($cert['issued_at']));
$cert_level  = $cert['cert_level_name'] ?? $cert['certification_name'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Certificate – <?= htmlspecialchars($cert['player_name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#1e1b4b;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;padding:30px 20px;font-family:'Lato',sans-serif}

.actions{display:flex;gap:12px;margin-bottom:24px}
.btn{padding:10px 24px;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;text-decoration:none;transition:opacity .2s}
.btn-print{background:#7c3aed;color:white}
.btn-back{background:rgba(255,255,255,.15);color:white}
.btn:hover{opacity:.85}

/* Certificate card */
.cert-wrap{
    position:relative;
    background:#fffdf4;
    width:100%;
    max-width:800px;
    border-radius:4px;
    padding:10px;
    box-shadow:0 25px 60px rgba(0,0,0,.5);
}

.cert-inner{
    border:3px solid #7c3aed;
    outline:6px solid #a855f7;
    outline-offset:-12px;
    padding:50px 60px;
    min-height:560px;
    display:flex;
    flex-direction:column;
    align-items:center;
    text-align:center;
    position:relative;
    overflow:hidden;
}

/* Corner ornaments */
.cert-inner::before,.cert-inner::after{
    content:'✦';
    position:absolute;
    font-size:30px;
    color:#a855f7;
    opacity:.4;
}
.cert-inner::before{top:18px;left:22px}
.cert-inner::after{bottom:18px;right:22px}

.org-name{
    font-family:'Cinzel',serif;
    font-size:13px;
    letter-spacing:4px;
    text-transform:uppercase;
    color:#7c3aed;
    margin-bottom:8px;
}

.cert-title{
    font-family:'Cinzel',serif;
    font-size:clamp(22px,4vw,34px);
    font-weight:700;
    color:#1e1b4b;
    letter-spacing:2px;
    margin-bottom:6px;
}

.cert-subtitle{
    font-size:12px;
    letter-spacing:3px;
    text-transform:uppercase;
    color:#6d28d9;
    margin-bottom:36px;
}

.presented-to{
    font-size:14px;
    color:#6b7280;
    letter-spacing:2px;
    text-transform:uppercase;
    margin-bottom:8px;
}

.student-name{
    font-family:'Cinzel',serif;
    font-size:clamp(26px,5vw,42px);
    font-weight:700;
    color:#1e1b4b;
    border-bottom:2px solid #7c3aed;
    padding-bottom:8px;
    margin-bottom:28px;
    width:80%;
}

.cert-body{
    font-size:15px;
    color:#374151;
    line-height:1.7;
    max-width:560px;
    margin-bottom:24px;
}

.exam-title{
    font-weight:700;
    color:#1e1b4b;
    font-size:17px;
}

<?php if ($cert_level): ?>
.cert-level-badge{
    display:inline-block;
    background:linear-gradient(135deg,#7c3aed,#a855f7);
    color:white;
    padding:8px 24px;
    border-radius:99px;
    font-size:14px;
    font-weight:700;
    letter-spacing:1px;
    margin-bottom:24px;
}
<?php endif; ?>

.score-row{
    display:flex;
    gap:32px;
    margin-bottom:32px;
}
.score-box{
    text-align:center;
}
.score-val{
    font-family:'Cinzel',serif;
    font-size:28px;
    font-weight:700;
    color:#7c3aed;
}
.score-lbl{
    font-size:11px;
    letter-spacing:2px;
    text-transform:uppercase;
    color:#9ca3af;
    margin-top:4px;
}

.divider{
    width:80%;
    height:1px;
    background:linear-gradient(90deg,transparent,#a855f7,transparent);
    margin:0 auto 28px;
}

.cert-footer{
    display:flex;
    justify-content:space-between;
    width:100%;
    align-items:flex-end;
    margin-top:auto;
}
.sig-line{
    text-align:center;
    width:180px;
}
.sig-bar{
    border-top:1.5px solid #374151;
    padding-top:8px;
    font-size:12px;
    color:#6b7280;
    letter-spacing:1px;
}
.cert-verify{
    font-size:10px;
    color:#9ca3af;
    letter-spacing:1px;
    text-align:center;
}
.cert-seal{
    font-size:52px;
    line-height:1;
}

@media print{
    body{background:white;padding:0}
    .actions{display:none}
    .cert-wrap{box-shadow:none;max-width:100%;padding:0}
    .cert-inner{outline:4px solid #a855f7;padding:36px 48px}
}
</style>
    <link rel="stylesheet" href="/Exam-mis/exams/assets/exam-theme.css">
</head>
<body class="exam-dark">
    <?php $back_to = 'results.php'; $back_label = 'Results'; include('nav_back.php'); ?>

<div class="actions">
    <button class="btn btn-print" onclick="window.print()">🖨 Print Certificate</button>
    <a class="btn btn-back" href="results.php">← Back to Results</a>
</div>

<div class="cert-wrap">
<div class="cert-inner">

    <div class="org-name">ICRPplus Learning Institute</div>

    <div class="cert-title">Certificate of Achievement</div>
    <div class="cert-subtitle">This document certifies that</div>

    <div class="presented-to">Presented to</div>
    <div class="student-name"><?= htmlspecialchars($cert['player_name']) ?></div>

    <div class="cert-body">
        has successfully completed the examination on<br>
        <span class="exam-title"><?= htmlspecialchars($cert['exam_title']) ?></span>
        <?php if ($cert_level): ?>
        <br>earning the <strong><?= htmlspecialchars($cert_level) ?></strong>
        <?php endif; ?>
    </div>

    <?php if ($cert_level): ?>
    <div class="cert-level-badge">🏅 <?= htmlspecialchars($cert_level) ?></div>
    <?php endif; ?>

    <div class="score-row">
        <div class="score-box">
            <div class="score-val"><?= $cert['percentage'] ?>%</div>
            <div class="score-lbl">Score</div>
        </div>
        <div class="score-box">
            <div class="score-val"><?= $cert['score'] ?>/<?= $cert['total_marks'] ?></div>
            <div class="score-lbl">Points</div>
        </div>
        <div class="score-box">
            <div class="score-val"><?= $issued_date ?></div>
            <div class="score-lbl">Date Issued</div>
        </div>
    </div>

    <div class="divider"></div>

    <div class="cert-footer">
        <div class="sig-line">
            <div class="sig-bar">Authorized Signature</div>
        </div>
        <div class="cert-verify">
            <div class="cert-seal">🎓</div>
            Verification Code<br>
            <strong><?= htmlspecialchars($cert['cert_code']) ?></strong>
        </div>
        <div class="sig-line">
            <div class="sig-bar">Date: <?= $issued_date ?></div>
        </div>
    </div>

</div>
</div>

</body>
</html>
