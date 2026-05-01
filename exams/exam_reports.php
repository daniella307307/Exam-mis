<?php
/**
 * Exam scoreboard download.
 *
 * Outputs an Excel-friendly file (.xls) instead of CSV. Excel happily
 * opens an HTML table served as application/vnd.ms-excel, which keeps
 * us dependency-free while still giving the user something they can
 * double-click and have it open in Excel/LibreOffice with proper
 * columns and formatting (no manual "import as CSV" step).
 *
 * Optional ?exam_id=X — defaults to the latest exam if not supplied.
 */
include('../db.php');

$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
if ($exam_id > 0) {
    $stmt = $conn->prepare("SELECT exam_id, title, topic, grade, exam_code FROM exams WHERE exam_id = ? LIMIT 1");
    $stmt->bind_param('i', $exam_id);
} else {
    $stmt = $conn->prepare("SELECT exam_id, title, topic, grade, exam_code FROM exams ORDER BY created_at DESC LIMIT 1");
}
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$exam) {
    http_response_code(404);
    die('No exam found.');
}

$exam_id    = (int)$exam['exam_id'];
$exam_title = (string)$exam['title'];

$stmt = $conn->prepare("SELECT COALESCE(SUM(marks),0) AS total_marks FROM questions WHERE exam_id = ?");
$stmt->bind_param('i', $exam_id);
$stmt->execute();
$total_marks = (int)$stmt->get_result()->fetch_assoc()['total_marks'];
$stmt->close();

$stmt = $conn->prepare(
    "SELECT nickname, score, grade, COALESCE(stream,'') AS stream, school
       FROM players
      WHERE exam_id = ?
      ORDER BY score DESC, player_id ASC"
);
$stmt->bind_param('i', $exam_id);
$stmt->execute();
$players = $stmt->get_result();

$safe_filename = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $exam_title);
if ($safe_filename === '') { $safe_filename = 'exam_' . $exam_id; }
$filename = $safe_filename . '_results.xls';

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

echo "\xEF\xBB\xBF"; // UTF-8 BOM so Excel reads accents correctly.
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<style>
    table { border-collapse: collapse; font-family: Calibri, Arial, sans-serif; }
    th { background:#4f46e5; color:#ffffff; padding:8px 12px; border:1px solid #312e81; text-align:left; }
    td { padding:6px 10px; border:1px solid #d1d5db; }
    .pass { color:#15803d; font-weight:700; }
    .fail { color:#b91c1c; font-weight:700; }
    .num  { mso-number-format:"0"; text-align:right; }
    .pct  { mso-number-format:"0\\%"; text-align:right; }
    h2    { font-family: Calibri, Arial, sans-serif; }
</style>
</head>
<body>
<h2><?= htmlspecialchars($exam_title, ENT_QUOTES, 'UTF-8') ?> &mdash; Results</h2>
<p>Topic: <?= htmlspecialchars((string)$exam['topic'], ENT_QUOTES, 'UTF-8') ?>
   &nbsp;·&nbsp; Grade: <?= htmlspecialchars((string)$exam['grade'], ENT_QUOTES, 'UTF-8') ?>
   &nbsp;·&nbsp; Total marks: <?= $total_marks ?></p>
<table>
<thead>
<tr>
    <th>Rank</th>
    <th>Name</th>
    <th>Score</th>
    <th>Total Marks</th>
    <th>Percentage</th>
    <th>Grade</th>
    <th>Stream</th>
    <th>School</th>
    <th>Result</th>
</tr>
</thead>
<tbody>
<?php
$rank = 1;
while ($row = $players->fetch_assoc()):
    $score   = (int)$row['score'];
    $pct     = ($total_marks > 0) ? (int)round(($score / $total_marks) * 100) : 0;
    $passed  = $pct >= 50;
?>
<tr>
    <td class="num"><?= $rank++ ?></td>
    <td><?= htmlspecialchars((string)$row['nickname'], ENT_QUOTES, 'UTF-8') ?></td>
    <td class="num"><?= $score ?></td>
    <td class="num"><?= $total_marks ?></td>
    <td class="pct"><?= $pct ?></td>
    <td><?= htmlspecialchars((string)$row['grade'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars((string)$row['stream'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars((string)$row['school'], ENT_QUOTES, 'UTF-8') ?></td>
    <td class="<?= $passed ? 'pass' : 'fail' ?>"><?= $passed ? 'Pass' : 'Fail' ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</body>
</html>
<?php
$conn->close();
exit;
