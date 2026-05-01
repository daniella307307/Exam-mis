<?php
session_start();
$_SESSION['user_role'] = 'teacher';

include('../layout/header.php');
include('../../db.php');

$exam_id = $_GET['exam_id'] ?? null;

if (!$exam_id) {
    // Show list of exams to choose from
    $query = "SELECT exam_id, title, exam_code, grade, created_at FROM exams ORDER BY created_at DESC LIMIT 50";
    $result = $conn->query($query);
    $exams_list = [];
    while ($row = $result->fetch_assoc()) {
        $exams_list[] = $row;
    }
    ?>
    <style>
        .exam-selector {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .exam-selector h2 {
            margin-bottom: 24px;
            color: var(--text);
        }

        .exams-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .exam-option {
            padding: 20px;
            border: 2px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }

        .exam-option:hover {
            border-color: var(--primary);
            background: rgba(102,126,234,0.05);
            transform: translateY(-2px);
        }

        .exam-option h3 {
            color: var(--primary);
            margin-bottom: 8px;
        }

        .exam-option p {
            font-size: 13px;
            color: var(--muted);
            margin: 4px 0;
        }
    </style>

    <div class="exam-selector">
        <h2>📋 Select Exam to View Class Report</h2>
        <div class="exams-list">
            <?php foreach ($exams_list as $exam): ?>
                <a href="?exam_id=<?= $exam['exam_id'] ?>" class="exam-option">
                    <h3><?= htmlspecialchars($exam['title']) ?></h3>
                    <p>Code: <strong><?= $exam['exam_code'] ?></strong></p>
                    <p>Grade: <?= htmlspecialchars($exam['grade']) ?></p>
                    <p>Created: <?= date('M d, Y', strtotime($exam['created_at'])) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include('../layout/footer.php');
    exit;
}

// Get exam details
$exam_stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
$exam_stmt->bind_param("i", $exam_id);
$exam_stmt->execute();
$exam = $exam_stmt->get_result()->fetch_assoc();

if (!$exam) {
    die("Exam not found");
}

// Get all students who took this exam with their scores
$students_stmt = $conn->prepare("
    SELECT 
        p.player_id,
        p.nickname,
        p.grade,
        p.school,
        p.stream,
        p.score,
        COUNT(a.answer_id) as questions_answered,
        SUM(a.is_correct) as correct_answers
    FROM players p
    LEFT JOIN answers a ON p.player_id = a.player_id AND a.exam_id = ?
    WHERE p.exam_id = ?
    GROUP BY p.player_id, p.nickname, p.grade, p.school, p.stream, p.score
    ORDER BY p.score DESC
");
$students_stmt->bind_param("ii", $exam_id, $exam_id);
$students_stmt->execute();
$students_result = $students_stmt->get_result();
$students = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
}

// Get total marks for percentage calculation
$marks_stmt = $conn->prepare("SELECT SUM(marks) as total_marks FROM questions WHERE exam_id = ?");
$marks_stmt->bind_param("i", $exam_id);
$marks_stmt->execute();
$marks_result = $marks_stmt->get_result();
$total_marks = $marks_result->fetch_assoc()['total_marks'] ?? 0;

// Calculate statistics
$pass_count = count(array_filter($students, fn($s) => ($s['score'] / max($total_marks, 1)) * 100 >= 50));
$avg_score = count($students) > 0 ? array_sum(array_column($students, 'score')) / count($students) : 0;

// Get filter
$grade_filter = $_GET['grade'] ?? '';
if (!empty($grade_filter)) {
    $students = array_filter($students, fn($s) => $s['grade'] === $grade_filter);
}
?>

<style>
    .report-header {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .report-title {
        font-size: 28px;
        color: var(--text);
        margin-bottom: 20px;
    }

    .report-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .info-item {
        border-left: 3px solid var(--primary);
        padding-left: 16px;
    }

    .info-label {
        font-size: 12px;
        color: var(--muted);
        text-transform: uppercase;
    }

    .info-value {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
        margin-top: 4px;
    }

    .report-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
    }

    .stat-box {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }

    .stat-box h3 {
        font-size: 28px;
        margin-bottom: 6px;
    }

    .stat-box p {
        font-size: 12px;
        opacity: 0.9;
    }

    .results-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        color: white;
    }

    th {
        padding: 16px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
    }

    td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        font-size: 14px;
    }

    tbody tr:hover {
        background: var(--bg);
    }

    .rank {
        font-weight: 700;
        color: var(--primary);
        width: 40px;
    }

    .student-name {
        font-weight: 600;
        color: var(--text);
    }

    .score {
        font-weight: 600;
    }

    .percentage {
        font-weight: 600;
    }

    .percentage.pass {
        color: #28a745;
    }

    .percentage.fail {
        color: #dc3545;
    }

    .status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .status.pass {
        background: rgba(40,167,69,0.2);
        color: #28a745;
    }

    .status.fail {
        background: rgba(220,53,69,0.2);
        color: #dc3545;
    }

    .export-btn {
        background: var(--primary);
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }

    .export-btn:hover {
        background: var(--accent);
        transform: translateY(-2px);
    }

    @media print {
        nav, .main-footer, .export-btn {
            display: none;
        }

        .report-header {
            box-shadow: none;
            border: 1px solid #ddd;
        }
    }
</style>

<script>
    function setBreadcrumb([
        {name: '👨‍🏫 Reports', url: '../teacher/dashboard.php'},
        {name: '📋 Class Report', url: '#'}
    ]);
</script>

<div class="report-header">
    <div class="report-title">📋 Class Report: <?= htmlspecialchars($exam['title']) ?></div>
    
    <div class="report-info">
        <div class="info-item">
            <div class="info-label">Exam Code</div>
            <div class="info-value"><?= $exam['exam_code'] ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Marks</div>
            <div class="info-value"><?= $total_marks ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Grade Level</div>
            <div class="info-value"><?= htmlspecialchars($exam['grade']) ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Participants</div>
            <div class="info-value"><?= count($students) ?></div>
        </div>
    </div>

    <div class="report-stats">
        <div class="stat-box">
            <h3><?= count($students) ?></h3>
            <p>Total Students</p>
        </div>
        <div class="stat-box">
            <h3><?= $pass_count ?></h3>
            <p>Passed (≥50%)</p>
        </div>
        <div class="stat-box">
            <h3><?= count($students) - $pass_count ?></h3>
            <p>Failed</p>
        </div>
        <div class="stat-box">
            <h3><?= round($avg_score) ?></h3>
            <p>Average Score</p>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <button class="export-btn" onclick="window.print()">🖨️ Print / Export as PDF</button>
    </div>
</div>

<div class="results-table">
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">Rank</th>
                <th>Student Name</th>
                <th>Grade</th>
                <th>Score</th>
                <th>Percentage</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $rank = 1;
            foreach ($students as $student): 
                $percentage = ($total_marks > 0) ? ($student['score'] / $total_marks) * 100 : 0;
                $status = $percentage >= 50 ? 'pass' : 'fail';
            ?>
                <tr>
                    <td class="rank"><?= $rank++ ?></td>
                    <td class="student-name"><?= htmlspecialchars($student['nickname']) ?></td>
                    <td><?= htmlspecialchars($student['grade'] ?? 'N/A') ?></td>
                    <td class="score"><?= $student['score'] ?>/<?= $total_marks ?></td>
                    <td class="percentage <?= $status ?>">
                        <?= round($percentage) ?>%
                    </td>
                    <td>
                        <span class="status <?= $status ?>">
                            <?= $status === 'pass' ? '✓ Pass' : '✗ Fail' ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('../layout/footer.php'); ?>
