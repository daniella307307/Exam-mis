<?php
session_start();
$_SESSION['user_role'] = 'teacher';

include('../layout/header.php');
include('../../db.php');
require_once(__DIR__ . '/../lib/exam_acl.php');

// Identify the teacher viewing this page. Without a login we have nothing
// to scope by, so bounce to the front door.
$acl = exam_acl_context($conn);
if (!$acl['user_id']) {
    header('Location: ' . APP_BASE_URL . '/index.php');
    exit;
}
$user_id   = (int)$acl['user_id'];
$school_id = (int)($acl['school_id'] ?? 0);

// ----------------------------------------------------------------------
// View routing: "mine" (default — only exams I created) vs "public" (a
// library of exams from other teachers, with the original author credited
// on every card).
// ----------------------------------------------------------------------
$view          = ($_GET['view'] ?? 'mine') === 'public' ? 'public' : 'mine';
$search        = trim((string)($_GET['q'] ?? ''));
$status_filter = $_GET['status'] ?? '';
$grade_filter  = $_GET['grade']  ?? '';
$stream_filter = $_GET['stream'] ?? '';

// Idempotent: make sure the column the "Adapted from" credit relies on
// exists. The migration in db_backup/cloned_from_migration.sql does the
// same thing but this lets the dashboard still render before the SQL is
// applied manually.
$col_check = $conn->query("SHOW COLUMNS FROM `exams` LIKE 'cloned_from_exam_id'");
$has_cloned_from = ($col_check && $col_check->num_rows > 0);
if ($col_check) { $col_check->close(); }
if (!$has_cloned_from) {
    @ $conn->query("ALTER TABLE `exams` ADD COLUMN `cloned_from_exam_id` INT NULL DEFAULT NULL");
    $has_cloned_from = true;
}

// Common SELECT — credits original author (owner_name) and exposes the
// "adapted from" provenance for republished exams.
$cloned_from_select = $has_cloned_from
    ? "e.cloned_from_exam_id,
       COALESCE(CONCAT(orig_u.firstname,' ',orig_u.lastname), '') AS adapted_from_name,
       COALESCE(orig_s.school_name, '') AS adapted_from_school"
    : "NULL AS cloned_from_exam_id, '' AS adapted_from_name, '' AS adapted_from_school";

$select_cols = "
    e.exam_id,
    e.exam_code,
    e.title,
    e.topic,
    e.grade,
    e.status,
    e.created_at,
    e.created_by,
    e.school_id,
    e.is_public,
    COALESCE(CONCAT(u.firstname,' ',u.lastname), '') AS owner_name,
    COALESCE(s.school_name, '') AS school_name,
    $cloned_from_select,
    COUNT(DISTINCT p.player_id) AS student_count,
    AVG(p.score) AS avg_score,
    GROUP_CONCAT(DISTINCT NULLIF(p.stream, '')) AS streams,
    (SELECT SUM(marks) FROM questions WHERE exam_id = e.exam_id) AS total_marks
";

$group_by = "
    GROUP BY e.exam_id, e.exam_code, e.title, e.topic, e.grade, e.status, e.created_at,
             e.created_by, e.school_id, e.is_public,
             u.firstname, u.lastname, s.school_name"
    . ($has_cloned_from
        ? ", e.cloned_from_exam_id, orig_u.firstname, orig_u.lastname, orig_s.school_name"
        : "")
    . "
    ORDER BY e.created_at DESC
    LIMIT 200
";

// JOIN block — original-creator JOINs only added when the cloned_from
// column exists (otherwise the orig_* aliases would crash the parser).
$base_joins = "
    LEFT JOIN users   u ON u.user_id   = e.created_by
    LEFT JOIN schools s ON s.school_id = e.school_id
    LEFT JOIN players p ON p.exam_id   = e.exam_id"
    . ($has_cloned_from ? "
    LEFT JOIN exams   orig_e ON orig_e.exam_id   = e.cloned_from_exam_id
    LEFT JOIN users   orig_u ON orig_u.user_id   = orig_e.created_by
    LEFT JOIN schools orig_s ON orig_s.school_id = orig_e.school_id" : "");

if ($view === 'mine') {
    // "My Exams" now = mine OR my school's. Same-school co-teachers are
    // treated as joint authors (per the user's request) so Daniella sees
    // Shafii's exams under her own "My Exams" tab too.
    $params = [];
    $types  = '';

    if ($acl['is_developer']) {
        $where = "WHERE e.created_by = ?";
        $types   .= 'i';
        $params[] = $user_id;
    } else {
        $where = "WHERE e.created_by = ?
                     OR (? > 0 AND e.school_id = ?)";
        $types   .= 'iii';
        $params[] = $user_id;
        $params[] = $school_id;
        $params[] = $school_id;
    }

    $sql = "SELECT $select_cols
              FROM exams e
              $base_joins
             $where
             $group_by";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
} else {
    // Public Library = every exam flagged is_public = 1, regardless of who
    // owns it. Owners explicitly asked to see their own public exams here
    // too (visible confirmation that the toggle worked). Same-school exams
    // that aren't flagged Public still live under My Exams only.
    $params = [];
    $types  = '';
    $where  = "WHERE e.is_public = 1";

    if ($search !== '') {
        $where  .= " AND (e.title LIKE ?
                       OR e.topic LIKE ?
                       OR e.grade LIKE ?
                       OR COALESCE(CONCAT(u.firstname,' ',u.lastname),'') LIKE ?
                       OR COALESCE(s.school_name,'') LIKE ?)";
        $types  .= 'sssss';
        $like = '%' . $search . '%';
        $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    }

    $sql = "SELECT $select_cols
              FROM exams e
              $base_joins
             $where
             $group_by";
    $stmt = $conn->prepare($sql);
    // bind_param errors on empty types; with no search active we have no params.
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
}

$stmt->execute();
$result = $stmt->get_result();
$exams = [];
while ($row = $result->fetch_assoc()) {
    $exams[] = $row;
}
$stmt->close();

// Distinct streams (from players who actually joined exams) for the filter dropdown
$stream_options = [];
$streams_res = $conn->query("SELECT DISTINCT stream FROM players WHERE stream IS NOT NULL AND stream <> '' ORDER BY stream");
if ($streams_res) {
    while ($r = $streams_res->fetch_assoc()) {
        $stream_options[] = $r['stream'];
    }
}

// Apply the secondary status/grade/stream filters in PHP so we don't have
// to rewrite the dynamic query for each combination.
if (!empty($status_filter) || !empty($grade_filter) || !empty($stream_filter)) {
    $exams = array_filter($exams, function($e) use ($status_filter, $grade_filter, $stream_filter) {
        $match_status = empty($status_filter) || $e['status'] === $status_filter;
        $match_grade  = empty($grade_filter)  || $e['grade']  === $grade_filter;
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

<a href="<?= APP_BASE_URL ?>/Auth/SF/index.php"
   style="display:inline-flex;align-items:center;gap:8px;margin-bottom:14px;padding:10px 18px;background:rgba(255,255,255,.08);color:#fff;font-weight:700;font-size:13px;border-radius:8px;text-decoration:none;border:1px solid rgba(168,85,247,.3)">
    ← Back to LMS Home
</a>
<div class="dashboard-header">
    <h1 style="color:#fff">
        👨‍🏫 <?= $view === 'public' ? 'Public Library' : 'My Exams & Results' ?>
    </h1>
    <a href="../exam_creator_working.php" class="create-btn" style="color:#fff;font-weight:800">+ Create New Exam</a>
</div>

<!-- Tab switcher: My Exams (default) vs Public Library.
     Public lists same-school + globally-public exams from other teachers,
     each card credits the original author by name. -->
<div class="tab-bar" style="display:flex;gap:10px;margin-bottom:22px;flex-wrap:wrap;align-items:center;">
    <a href="?view=mine"
       class="tab-pill <?= $view === 'mine' ? 'tab-active' : '' ?>">
        📂 My Exams
    </a>
    <a href="?view=public"
       class="tab-pill <?= $view === 'public' ? 'tab-active' : '' ?>">
        🌐 Public Library
    </a>

    <?php if ($view === 'public'): ?>
        <form method="GET" style="margin-left:auto;display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="view" value="public">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search title, topic, grade, or teacher…"
                   style="padding:9px 14px;border-radius:8px;border:1.5px solid rgba(168,85,247,.35);background:rgba(15,15,40,.55);color:#f1f5f9;font-weight:600;font-size:13px;min-width:280px;">
            <button type="submit"
                    style="padding:9px 16px;border-radius:8px;border:none;background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;font-weight:800;font-size:13px;cursor:pointer;">
                Search
            </button>
            <?php if ($search !== ''): ?>
                <a href="?view=public" style="color:#fca5a5;font-size:12px;font-weight:700;text-decoration:underline;">Clear</a>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<style>
    .tab-pill {
        display:inline-flex; align-items:center; gap:8px;
        padding:10px 20px; border-radius:99px;
        background:rgba(255,255,255,.06);
        color:#cbd5e1; font-weight:700; font-size:14px;
        text-decoration:none;
        border:1px solid rgba(168,85,247,.25);
        transition:transform .15s ease, background .15s ease, color .15s ease;
    }
    .tab-pill:hover { background:rgba(168,85,247,.18); color:#fff; transform:translateY(-1px); }
    .tab-pill.tab-active {
        background:linear-gradient(135deg,#7c3aed,#a855f7);
        color:#fff;
        border-color:rgba(168,85,247,.6);
        box-shadow:0 8px 22px rgba(124,58,237,.35);
    }
    .credit-chip {
        display:inline-flex; align-items:center; gap:6px;
        padding:4px 10px; border-radius:999px;
        background:rgba(124,58,237,.18);
        color:#c4b5fd; font-size:12px; font-weight:700;
        border:1px solid rgba(168,85,247,.35);
        margin-top:6px;
    }
    .origin-badge {
        display:inline-block;
        padding:3px 9px; border-radius:999px;
        font-size:11px; font-weight:800; letter-spacing:.4px;
        text-transform:uppercase;
        margin-left:6px;
    }
    .origin-school { background:rgba(59,130,246,.18); color:#93c5fd; border:1px solid rgba(59,130,246,.4); }
    .origin-public { background:rgba(16,185,129,.18); color:#6ee7b7; border:1px solid rgba(16,185,129,.4); }
    .pub-toggle {
        cursor:pointer; border:none;
        padding:6px 12px; border-radius:999px;
        font-weight:800; font-size:11px; letter-spacing:.4px;
        text-transform:uppercase;
        margin-top:10px;
    }
    .pub-toggle.on  { background:rgba(16,185,129,.18); color:#6ee7b7; border:1px solid rgba(16,185,129,.4); }
    .pub-toggle.off { background:rgba(148,163,184,.18); color:#cbd5e1; border:1px solid rgba(148,163,184,.4); }
    .pub-toggle:hover { transform:translateY(-1px); }
    .pub-toggle:disabled { opacity:.55; cursor:wait; }

    /* Adapted-from chip: cards that are a republish of another teacher's exam. */
    .adapted-chip {
        display:inline-flex; align-items:center; gap:6px;
        padding:3px 10px; border-radius:999px;
        background:rgba(245,158,11,.16); color:#fde68a;
        font-size:11px; font-weight:800; letter-spacing:.3px;
        border:1px solid rgba(245,158,11,.4);
        margin-top:6px;
    }
    .public-action-row {
        display:flex; gap:8px; margin-top:12px; flex-wrap:wrap;
    }
    .preview-btn, .republish-btn-card {
        flex:1; min-width:120px;
        padding:10px 12px; border-radius:8px; border:none;
        font-weight:800; font-size:12px; cursor:pointer;
        text-decoration:none; text-align:center;
    }
    .preview-btn  { background:rgba(59,130,246,.18); color:#93c5fd; border:1px solid rgba(59,130,246,.4); }
    .preview-btn:hover  { background:rgba(59,130,246,.28); }
    .republish-btn-card { background:linear-gradient(135deg,#10b981,#22c55e); color:#fff; box-shadow:0 6px 16px rgba(16,185,129,.3); }
    .republish-btn-card:hover { transform:translateY(-1px); }
</style>

<div class="filters" style="margin-bottom: 30px;">
    <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
        <!-- Preserve the active tab when applying secondary filters. -->
        <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">
        <?php if ($view === 'public' && $search !== ''): ?>
            <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
        <?php endif; ?>
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
            <!-- Streams are fixed A–E across the whole platform. -->
            <select name="stream">
                <option value="">All Streams</option>
                <?php foreach (['A','B','C','D','E'] as $s): ?>
                    <option value="<?= $s ?>" <?= $stream_filter === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="action-btn">🔍 Filter</button>
        <a href="?view=<?= htmlspecialchars($view) ?>" class="action-btn" style="border-color: var(--primary); color: var(--primary);">↺ Reset</a>
    </form>
</div>

<?php if (empty($exams)): ?>
    <div class="empty-state">
        <?php if ($view === 'public'): ?>
            <h2>📭 Nothing in the Public Library yet</h2>
            <p>
                <?= $search !== ''
                    ? 'No exam matches "' . htmlspecialchars($search) . '". Try a different keyword.'
                    : 'When a teacher marks their exam Public, it will appear here with their name credited.' ?>
            </p>
        <?php else: ?>
            <h2>📭 No Exams Yet</h2>
            <p>Create your first exam to get started, or
               <a href="?view=public" style="color:#a78bfa;font-weight:700;text-decoration:underline;">browse the Public Library</a>.</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="exams-grid">
        <?php foreach ($exams as $exam):
            // Strict author check (used for credit chip / public toggle visibility).
            $is_actual_creator = ((int)$exam['created_by'] === $user_id);
            $exam_school   = (int)($exam['school_id'] ?? 0);
            // "Co-owner" — same school, treated as joint owner under the
            // widened ACL. Gets the full edit/manage UI even on My Exams.
            $is_co_owner    = !$is_actual_creator && $school_id > 0 && $exam_school > 0 && $school_id === $exam_school;
            $is_pub         = (int)($exam['is_public'] ?? 0) === 1;
            $creator_name   = trim((string)($exam['owner_name'] ?? '')) ?: 'colleague';
            $school_name    = trim((string)($exam['school_name'] ?? ''));

            // Republished-from-public credit (only set if cloned_from_exam_id is present).
            $adapted_name   = trim((string)($exam['adapted_from_name'] ?? ''));
            $adapted_school = trim((string)($exam['adapted_from_school'] ?? ''));
            $is_adapted     = $adapted_name !== '';
        ?>
            <div class="exam-card <?= strtolower($exam['status']) ?>">
                <div class="exam-header">
                    <div>
                        <div class="exam-title">
                            <?= htmlspecialchars($exam['title']) ?>
                            <?php if ($view === 'public'): ?>
                                <span class="origin-badge origin-public">🌐 Public</span>
                            <?php elseif ($is_co_owner): ?>
                                <span class="origin-badge origin-school">🏫 Same school</span>
                            <?php elseif ($is_actual_creator && $is_pub): ?>
                                <span class="origin-badge origin-public">🌐 In public library</span>
                            <?php endif; ?>
                        </div>
                        <div class="exam-code">Code: <?= $exam['exam_code'] ?></div>

                        <?php if ($view === 'public'): ?>
                            <!-- Credit the teacher (and their school) on every public card. -->
                            <div class="credit-chip">
                                🧑‍🏫 By: <?= htmlspecialchars($creator_name) ?><?= $school_name !== '' ? ' @ ' . htmlspecialchars($school_name) : '' ?>
                            </div>
                        <?php elseif ($is_co_owner): ?>
                            <!-- Same-school colleague's exam — credit them but full access. -->
                            <div class="credit-chip">
                                🧑‍🏫 By: <?= htmlspecialchars($creator_name) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($is_adapted): ?>
                            <!-- This exam was republished from the Public Library. Keep the trail. -->
                            <div class="adapted-chip">
                                📝 Adapted from <?= htmlspecialchars($adapted_name) ?><?= $adapted_school !== '' ? ' @ ' . htmlspecialchars($adapted_school) : '' ?>
                            </div>
                        <?php endif; ?>
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

                <?php if ($view !== 'public'): ?>
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
                <?php endif; ?>

                <?php if ($view !== 'public' && ($is_actual_creator || $is_co_owner)): ?>
                    <!-- Owner / co-owner can flip the global Public flag. -->
                    <button type="button"
                            class="pub-toggle <?= $is_pub ? 'on' : 'off' ?>"
                            data-exam="<?= (int)$exam['exam_id'] ?>"
                            data-value="<?= $is_pub ? 1 : 0 ?>"
                            title="When ON, teachers at other schools can find this exam in the Public Library. The original author's name stays credited.">
                        🌐 Public: <strong class="pub-state"><?= $is_pub ? 'ON' : 'OFF' ?></strong>
                    </button>
                <?php endif; ?>

                <?php if ($view === 'public'): ?>
                    <!-- Cross-school exam — preview-then-republish flow. -->
                    <div class="public-action-row">
                        <a href="../preview_public_exam.php?exam_id=<?= (int)$exam['exam_id'] ?>"
                           class="preview-btn">👁 Preview</a>
                        <button type="button"
                                class="republish-btn-card"
                                onclick="republishToMySchool(<?= (int)$exam['exam_id'] ?>)">
                            📥 Republish to my school
                        </button>
                    </div>
                <?php else: ?>
                    <div class="exam-actions">
                        <a href="../exams_dashboard.php" class="action-btn">Manage</a>
                        <a href="../teacher/class_report.php?exam_id=<?= $exam['exam_id'] ?>" class="action-btn">Results</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
// Per-exam Public toggle. Calls the same endpoint exams_dashboard.php uses.
document.querySelectorAll('.pub-toggle').forEach((btn) => {
    btn.addEventListener('click', async () => {
        if (btn.disabled) return;
        const examId  = parseInt(btn.dataset.exam, 10);
        const current = parseInt(btn.dataset.value, 10) ? 1 : 0;
        const next    = current ? 0 : 1;

        btn.disabled = true;
        const stateEl = btn.querySelector('.pub-state');
        if (stateEl) stateEl.textContent = '…';
        try {
            const fd = new FormData();
            fd.append('exam_id', examId);
            fd.append('value', next);
            const r = await fetch('<?= APP_BASE_URL ?>/exams/exam_public_toggle.php', { method: 'POST', body: fd });
            const j = await r.json();
            if (!j.success) throw new Error(j.error || 'save failed');
            btn.dataset.value = next;
            btn.classList.toggle('on',  next === 1);
            btn.classList.toggle('off', next === 0);
            if (stateEl) stateEl.textContent = next ? 'ON' : 'OFF';
        } catch (err) {
            alert('Could not save: ' + err.message);
            if (stateEl) stateEl.textContent = current ? 'ON' : 'OFF';
        } finally {
            btn.disabled = false;
        }
    });
});

// Republish a public exam into the current teacher's school. POST to
// clone_exam.php which deep-copies questions/options and returns the
// new exam_id. The new copy has a fresh leaderboard (no players yet).
async function republishToMySchool(examId) {
    if (!confirm('📥 Republish this exam to your school?\n\nA brand-new copy will be added to your dashboard. Your students start on a fresh leaderboard.')) return;
    try {
        const fd = new FormData();
        fd.append('exam_id', examId);
        const r = await fetch('<?= APP_BASE_URL ?>/exams/clone_exam.php', { method: 'POST', body: fd });
        const j = await r.json();
        if (!j.success) throw new Error(j.error || 'clone failed');
        alert(`✅ Republished!\n\nNew exam code: ${j.new_exam_code}\nQuestions copied: ${j.questions}\n\nTaking you to My Exams…`);
        window.location.href = '?view=mine';
    } catch (err) {
        alert('❌ Republish failed: ' + err.message);
    }
}
</script>

<?php include('../layout/footer.php'); ?>
