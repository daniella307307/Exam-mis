<?php
session_start();
$_SESSION['user_role'] = 'teacher';

include('../layout/header.php');
include('../../db.php');

// Get all exams created by teachers
$query = "
    SELECT
        e.exam_id,
        e.exam_code,
        e.title,
        e.topic,
        e.grade,
        e.status,
        e.created_at,
        COUNT(DISTINCT p.player_id) as student_count,
        AVG(p.score) as avg_score,
        GROUP_CONCAT(DISTINCT NULLIF(p.stream, '')) as streams,
        (SELECT SUM(marks) FROM questions WHERE exam_id = e.exam_id) as total_marks
    FROM exams e
    LEFT JOIN players p ON e.exam_id = p.exam_id
    GROUP BY e.exam_id, e.exam_code, e.title, e.topic, e.grade, e.status, e.created_at
    ORDER BY e.created_at DESC
    LIMIT 100
";

$result = $conn->query($query);
$exams = [];
while ($row = $result->fetch_assoc()) {
    $exams[] = $row;
}

// Distinct streams (from players who actually joined exams) for the filter dropdown
$stream_options = [];
$streams_res = $conn->query("SELECT DISTINCT stream FROM players WHERE stream IS NOT NULL AND stream <> '' ORDER BY stream");
if ($streams_res) {
    while ($r = $streams_res->fetch_assoc()) {
        $stream_options[] = $r['stream'];
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$grade_filter = $_GET['grade'] ?? '';
$stream_filter = $_GET['stream'] ?? '';

// Apply filters
if (!empty($status_filter) || !empty($grade_filter) || !empty($stream_filter)) {
    $exams = array_filter($exams, function($e) use ($status_filter, $grade_filter, $stream_filter) {
        $match_status = empty($status_filter) || $e['status'] === $status_filter;
        $match_grade = empty($grade_filter) || $e['grade'] === $grade_filter;
        $exam_streams = !empty($e['streams']) ? array_map('trim', explode(',', $e['streams'])) : [];
        $match_stream = empty($stream_filter) || in_array($stream_filter, $exam_streams, true);
        return $match_status && $match_grade && $match_stream;
    });
}
?>

<style>
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .dashboard-header h1 {
        font-size: 32px;
        color: var(--text);
    }

    .create-btn {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }

    .create-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    .filters {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-group label {
        font-size: 13px;
        color: var(--muted);
        text-transform: uppercase;
        font-weight: 600;
    }

    .filter-group select {
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 13px;
        background: white;
        color: var(--text);
    }

    .filter-group select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
    }

    .exams-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .exam-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s;
        border-left: 4px solid var(--primary);
    }

    .exam-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .exam-card.active {
        border-left-color: #28a745;
    }

    .exam-card.draft {
        border-left-color: #ffc107;
        opacity: 0.85;
    }

    .exam-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .exam-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 4px;
    }

    .exam-code {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: var(--muted);
        background: var(--bg);
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-block;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.active {
        background: rgba(40,167,69,0.2);
        color: #28a745;
    }

    .status-badge.draft {
        background: rgba(255,193,7,0.2);
        color: #ffc107;
    }

    .exam-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin: 16px 0;
        padding: 12px 0;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        font-size: 13px;
    }

    .meta-item {
        color: var(--muted);
    }

    .meta-value {
        color: var(--text);
        font-weight: 600;
    }

    .exam-stats {
        display: flex;
        gap: 12px;
        margin: 16px 0;
    }

    .stat {
        flex: 1;
        background: var(--bg);
        padding: 12px;
        border-radius: 6px;
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

    .exam-actions {
        display: flex;
        gap: 8px;
        margin-top: 16px;
    }

    .action-btn {
        flex: 1;
        padding: 10px 12px;
        border: 1px solid var(--border);
        background: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.3s;
        text-decoration: none;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .action-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .empty-state {
        text-align: center;
        padding: 60px 24px;
        color: var(--muted);
        background: white;
        border-radius: 12px;
    }

    .empty-state h2 {
        margin-bottom: 10px;
        color: var(--text);
    }

    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .exams-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    function setBreadcrumb([
        {name: '👨‍🏫 Teacher Dashboard', url: '#'}
    ]);
</script>

<a href="/Exam-mis/Auth/SF/index.php"
   style="display:inline-flex;align-items:center;gap:8px;margin-bottom:14px;padding:10px 18px;background:rgba(255,255,255,.08);color:#fff;font-weight:700;font-size:13px;border-radius:8px;text-decoration:none;border:1px solid rgba(168,85,247,.3)">
    ← Back to LMS Home
</a>
<div class="dashboard-header">
    <h1 style="color:#fff">👨‍🏫 My Exams & Results</h1>
    <a href="../exam_creator_working.php" class="create-btn" style="color:#fff;font-weight:800">+ Create New Exam</a>
</div>

<div class="filters" style="margin-bottom: 30px;">
    <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
        <div class="filter-group">
            <label>Status</label>
            <select name="status">
                <option value="">All</option>
                <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="ended" <?= $status_filter === 'ended' ? 'selected' : '' ?>>Ended</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Grade</label>
            <select name="grade">
                <option value="">All Grades</option>
                <option value="Grade 6" <?= $grade_filter === 'Grade 6' ? 'selected' : '' ?>>Grade 6</option>
                <option value="Grade 7" <?= $grade_filter === 'Grade 7' ? 'selected' : '' ?>>Grade 7</option>
                <option value="Grade 8" <?= $grade_filter === 'Grade 8' ? 'selected' : '' ?>>Grade 8</option>
                <option value="Grade 9" <?= $grade_filter === 'Grade 9' ? 'selected' : '' ?>>Grade 9</option>
                <option value="Grade 10" <?= $grade_filter === 'Grade 10' ? 'selected' : '' ?>>Grade 10</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Stream</label>
            <select name="stream">
                <option value="">All Streams</option>
                <?php foreach ($stream_options as $s): ?>
                    <option value="<?= htmlspecialchars($s) ?>" <?= $stream_filter === $s ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="action-btn">🔍 Filter</button>
        <a href="?" class="action-btn" style="border-color: var(--primary); color: var(--primary);">↺ Reset</a>
    </form>
</div>

<?php if (empty($exams)): ?>
    <div class="empty-state">
        <h2>📭 No Exams Yet</h2>
        <p>Create your first exam to get started. Click the button above to create a new exam.</p>
    </div>
<?php else: ?>
    <div class="exams-grid">
        <?php foreach ($exams as $exam): ?>
            <div class="exam-card <?= strtolower($exam['status']) ?>">
                <div class="exam-header">
                    <div>
                        <div class="exam-title"><?= htmlspecialchars($exam['title']) ?></div>
                        <div class="exam-code">Code: <?= $exam['exam_code'] ?></div>
                    </div>
                    <span class="status-badge <?= strtolower($exam['status']) ?>">
                        <?= ucfirst($exam['status']) ?>
                    </span>
                </div>

                <div class="exam-meta">
                    <div class="meta-item">
                        Topic: <span class="meta-value"><?= htmlspecialchars($exam['topic']) ?></span>
                    </div>
                    <div class="meta-item">
                        Grade: <span class="meta-value"><?= htmlspecialchars($exam['grade']) ?></span>
                    </div>
                    <div class="meta-item">
                        Created: <span class="meta-value"><?= date('M d, Y', strtotime($exam['created_at'])) ?></span>
                    </div>
                    <div class="meta-item">
                        Total Marks: <span class="meta-value"><?= $exam['total_marks'] ?? 'N/A' ?></span>
                    </div>
                </div>

                <div class="exam-stats">
                    <div class="stat">
                        <div class="stat-label">Students</div>
                        <div class="stat-value"><?= $exam['student_count'] ?></div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">Avg Score</div>
                        <div class="stat-value"><?= round($exam['avg_score'] ?? 0) ?></div>
                    </div>
                </div>

                <div class="exam-actions">
                    <a href="../exams_dashboard.php" class="action-btn">Manage</a>
                    <a href="../teacher/class_report.php?exam_id=<?= $exam['exam_id'] ?>" class="action-btn">Results</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include('../layout/footer.php'); ?>
