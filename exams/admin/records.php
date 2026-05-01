<?php
session_start();
$_SESSION['user_role'] = 'admin';

include('../layout/header.php');
include('../../db.php');

// Get comprehensive student records with all exams taken
$query = "
    SELECT 
        p.player_id,
        p.nickname,
        p.grade,
        p.school,
        p.stream,
        COUNT(DISTINCT p.exam_id) as total_exams,
        SUM(p.score) as total_score,
        ROUND(AVG(p.score / (SELECT SUM(q.marks) FROM questions q WHERE q.exam_id = p.exam_id)) * 100) as avg_percentage
    FROM players p
    GROUP BY p.player_id, p.nickname, p.grade, p.school, p.stream
    ORDER BY p.nickname ASC
    LIMIT 200
";

$result = $conn->query($query);
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Get search parameter
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $students = array_filter($students, fn($s) => stripos($s['nickname'], $search) !== false || stripos($s['grade'], $search) !== false);
}
?>

<style>
    .records-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .records-header h1 {
        font-size: 32px;
        color: var(--text);
    }

    .search-box {
        display: flex;
        gap: 8px;
    }

    .search-box input {
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 13px;
        width: 250px;
        max-width: 100%;
    }

    .search-box button {
        padding: 10px 20px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }

    .search-box button:hover {
        background: var(--accent);
    }

    .records-table {
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
        background: linear-gradient(135deg, #fd7e14 0%, #ff6b6b 100%);
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

    .student-name {
        font-weight: 600;
        color: var(--text);
    }

    .badge {
        display: inline-block;
        background: var(--bg);
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        color: var(--muted);
    }

    .exams-count {
        background: rgba(253,126,20,0.2);
        color: #fd7e14;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
    }

    .percentage {
        font-weight: 600;
        color: var(--primary);
    }

    .stats-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .stat-number {
        font-size: 32px;
        font-weight: bold;
        color: #fd7e14;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 13px;
        color: var(--muted);
        text-transform: uppercase;
    }

    .empty-state {
        text-align: center;
        padding: 60px 24px;
        background: white;
        border-radius: 12px;
    }

    @media print {
        nav, .main-footer, .records-header {
            display: none;
        }
    }
</style>

<script>
    function setBreadcrumb([
        {name: '🔐 Admin Records', url: '#'}
    ]);
</script>

<div class="records-header">
    <h1>🔐 Complete Student Records</h1>
    <div class="search-box">
        <form method="GET" style="display: flex; gap: 8px;">
            <input type="text" name="search" placeholder="Search student..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">🔍 Search</button>
            <?php if (!empty($search)): ?>
                <a href="?" style="padding: 10px 20px; background: var(--border); border-radius: 6px; text-decoration: none;">Reset</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="stats-summary">
    <div class="stat-card">
        <div class="stat-number"><?= count($students) ?></div>
        <div class="stat-label">Total Students</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php 
            $exams = $conn->query("SELECT COUNT(*) as count FROM exams")->fetch_assoc();
            echo $exams['count'];
        ?></div>
        <div class="stat-label">Total Exams</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php 
            $answers = $conn->query("SELECT COUNT(*) as count FROM answers")->fetch_assoc();
            echo $answers['count'];
        ?></div>
        <div class="stat-label">Total Responses</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= round(array_sum(array_column($students, 'avg_percentage')) / max(count($students), 1)) ?>%</div>
        <div class="stat-label">System Average</div>
    </div>
</div>

<?php if (empty($students)): ?>
    <div class="empty-state">
        <h2>📭 No Records Found</h2>
        <p>No student records available yet. Students will appear here after taking exams.</p>
    </div>
<?php else: ?>
    <div class="records-table">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Grade</th>
                    <th>School</th>
                    <th>Exams Taken</th>
                    <th>Total Score</th>
                    <th>Avg %</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td class="student-name"><?= htmlspecialchars($student['nickname']) ?></td>
                        <td>
                            <span class="badge"><?= htmlspecialchars($student['grade'] ?? 'N/A') ?></span>
                        </td>
                        <td><?= htmlspecialchars($student['school'] ?? 'N/A') ?></td>
                        <td>
                            <span class="exams-count"><?= $student['total_exams'] ?></span>
                        </td>
                        <td><?= $student['total_score'] ?></td>
                        <td>
                            <span class="percentage"><?= $student['avg_percentage'] ?>%</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
            🖨️ Print / Export as PDF
        </button>
    </div>
<?php endif; ?>

<?php include('../layout/footer.php'); ?>
