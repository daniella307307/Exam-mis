<?php
session_start();
$_SESSION['user_role'] = 'teacher';

include('../layout/header.php');
include('../../db.php');

$student_name = $_GET['name'] ?? null;

if (!$student_name) {
    // Show list of students
    $result = $conn->query("SELECT DISTINCT nickname, grade, school FROM players ORDER BY nickname ASC LIMIT 100");
    $students_list = [];
    while ($row = $result->fetch_assoc()) {
        $students_list[] = $row;
    }
    ?>

    <style>
        .student-selector {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .student-selector h2 {
            margin-bottom: 24px;
        }

        .students-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
        }

        .student-option {
            padding: 20px;
            border: 2px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }

        .student-option:hover {
            border-color: var(--primary);
            background: rgba(102,126,234,0.05);
            transform: translateY(-2px);
        }

        .student-option h3 {
            color: var(--primary);
            margin-bottom: 8px;
        }

        .student-option p {
            font-size: 13px;
            color: var(--muted);
            margin: 4px 0;
        }
    </style>

    <script>
        function setBreadcrumb([
            {name: '👨‍🏫 Reports', url: '../teacher/dashboard.php'},
            {name: '👤 Student Records', url: '#'}
        ]);
    </script>

    <div class="student-selector">
        <h2>👤 Select Student to View All Exams</h2>
        <div class="students-list">
            <?php foreach ($students_list as $student): ?>
                <a href="?name=<?= urlencode($student['nickname']) ?>" class="student-option">
                    <h3><?= htmlspecialchars($student['nickname']) ?></h3>
                    <p>Grade: <?= htmlspecialchars($student['grade'] ?? 'N/A') ?></p>
                    <p>School: <?= htmlspecialchars($student['school'] ?? 'N/A') ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include('../layout/footer.php');
    exit;
}

// Get all exams taken by this student
$query = "
    SELECT 
        e.exam_id,
        e.title,
        e.topic,
        e.exam_code,
        p.score,
        p.joined_at,
        COUNT(DISTINCT a.question_id) as questions_answered,
        SUM(a.is_correct) as correct_answers,
        (SELECT SUM(marks) FROM questions WHERE exam_id = e.exam_id) as total_marks
    FROM players p
    JOIN exams e ON p.exam_id = e.exam_id
    LEFT JOIN answers a ON p.player_id = a.player_id AND a.exam_id = e.exam_id
    WHERE p.nickname = ?
    GROUP BY e.exam_id, e.title, e.topic, e.exam_code, p.score, p.joined_at
    ORDER BY p.joined_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_name);
$stmt->execute();
$exams_result = $stmt->get_result();
$exams = [];
while ($row = $exams_result->fetch_assoc()) {
    $exams[] = $row;
}

// Get student info
$info_stmt = $conn->prepare("SELECT DISTINCT grade, school, stream FROM players WHERE nickname = ? LIMIT 1");
$info_stmt->bind_param("s", $student_name);
$info_stmt->execute();
$student_info = $info_stmt->get_result()->fetch_assoc();
?>

<style>
    .student-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
    }

    .student-header h1 {
        font-size: 28px;
        margin-bottom: 16px;
    }

    .student-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
    }

    .detail {
        background: rgba(255,255,255,0.1);
        padding: 12px;
        border-radius: 6px;
    }

    .detail-label {
        font-size: 12px;
        opacity: 0.9;
        margin-bottom: 4px;
    }

    .detail-value {
        font-size: 16px;
        font-weight: 700;
    }

    .exams-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .exam-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s;
    }

    .exam-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .exam-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 8px;
    }

    .exam-code {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: var(--muted);
        margin-bottom: 12px;
    }

    .exam-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 12px 0;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        margin: 12px 0;
    }

    .stat {
        text-align: center;
    }

    .stat-label {
        font-size: 12px;
        color: var(--muted);
    }

    .stat-value {
        font-size: 18px;
        font-weight: 700;
        color: var(--primary);
    }

    .percentage {
        font-weight: 600;
        font-size: 16px;
    }

    .percentage.pass {
        color: #28a745;
    }

    .percentage.fail {
        color: #dc3545;
    }

    .taken-date {
        font-size: 12px;
        color: var(--muted);
    }

    @media print {
        nav, .main-footer {
            display: none;
        }
    }
</style>

<script>
    function setBreadcrumb([
        {name: '👨‍🏫 Reports', url: '../teacher/dashboard.php'},
        {name: '👤 Student Records', url: '#'},
        {name: '<?= htmlspecialchars($student_name) ?>', url: '#'}
    ]);
</script>

<div class="student-header">
    <h1>👤 <?= htmlspecialchars($student_name) ?></h1>
    <div class="student-details">
        <div class="detail">
            <div class="detail-label">Grade</div>
            <div class="detail-value"><?= htmlspecialchars($student_info['grade'] ?? 'N/A') ?></div>
        </div>
        <div class="detail">
            <div class="detail-label">School</div>
            <div class="detail-value"><?= htmlspecialchars($student_info['school'] ?? 'N/A') ?></div>
        </div>
        <div class="detail">
            <div class="detail-label">Stream</div>
            <div class="detail-value"><?= htmlspecialchars($student_info['stream'] ?? 'N/A') ?></div>
        </div>
        <div class="detail">
            <div class="detail-label">Exams Taken</div>
            <div class="detail-value"><?= count($exams) ?></div>
        </div>
    </div>
</div>

<?php if (empty($exams)): ?>
    <div style="text-align: center; padding: 60px 24px; background: white; border-radius: 12px;">
        <h2>📭 No Exams Found</h2>
        <p>This student hasn't taken any exams yet.</p>
    </div>
<?php else: ?>
    <div class="exams-list">
        <?php foreach ($exams as $exam):
            $percentage = ($exam['total_marks'] > 0) ? ($exam['score'] / $exam['total_marks']) * 100 : 0;
            $status = $percentage >= 50 ? 'pass' : 'fail';
        ?>
            <div class="exam-card">
                <div class="exam-title"><?= htmlspecialchars($exam['title']) ?></div>
                <div class="exam-code">Code: <?= $exam['exam_code'] ?></div>

                <div class="exam-stats">
                    <div class="stat">
                        <div class="stat-label">Score</div>
                        <div class="stat-value"><?= $exam['score'] ?>/<?= $exam['total_marks'] ?></div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">Percentage</div>
                        <div class="stat-value percentage <?= $status ?>"><?= round($percentage) ?>%</div>
                    </div>
                </div>

                <div class="taken-date">
                    📅 Taken: <?= date('M d, Y H:i', strtotime($exam['joined_at'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
            🖨️ Print Report Card
        </button>
    </div>
<?php endif; ?>

<?php include('../layout/footer.php'); ?>
