<?php
require_once('../db_connection.php');
require_once(__DIR__ . '/lib/exam_acl.php');

$acl = exam_acl_context($conn);
if (!$acl['user_id']) {
    header('Location: ../index.php');
    exit;
}

// ----------------------------------------------------------------------
// View routing.
//   "mine"   (default) = exams I created OR exams from my school (because
//             co-teachers at the same school are joint owners under the
//             widened ACL — Daniella's exam IS Shafii's exam too).
//   "public" = exams from OTHER schools that the original teacher flagged
//             is_public = 1. Cross-school discovery + republish.
// Each public card credits the original author + their school.
// ----------------------------------------------------------------------
$view          = ($_GET['view'] ?? 'mine') === 'public' ? 'public' : 'mine';
$search        = trim((string)($_GET['q'] ?? ''));
$user_id       = (int)$acl['user_id'];
$school_id     = (int)($acl['school_id'] ?? 0);

// Idempotent: ensure exams.cloned_from_exam_id exists so the "Adapted from"
// chip renders without a manual migration step.
$col_check = $conn->query("SHOW COLUMNS FROM `exams` LIKE 'cloned_from_exam_id'");
$has_cloned_from = ($col_check && $col_check->num_rows > 0);
if ($col_check) { $col_check->close(); }
if (!$has_cloned_from) {
    @ $conn->query("ALTER TABLE `exams` ADD COLUMN `cloned_from_exam_id` INT NULL DEFAULT NULL");
    $has_cloned_from = true;
}

$cloned_from_select = $has_cloned_from
    ? "e.cloned_from_exam_id,
       COALESCE(CONCAT(orig_u.firstname,' ',orig_u.lastname), '') AS adapted_from_name,
       COALESCE(orig_s.school_name, '') AS adapted_from_school"
    : "NULL AS cloned_from_exam_id, '' AS adapted_from_name, '' AS adapted_from_school";

$select_cols = "e.exam_id, e.exam_code, e.title, e.topic, e.grade, e.status, e.created_at, e.start_time,
                e.is_active, e.is_public, e.created_by, e.school_id,
                COALESCE(CONCAT(u.firstname,' ',u.lastname), '') AS owner_name,
                COALESCE(s.school_name, '') AS school_name,
                $cloned_from_select";

$base_joins = "LEFT JOIN users   u ON u.user_id   = e.created_by
               LEFT JOIN schools s ON s.school_id = e.school_id"
    . ($has_cloned_from ? "
               LEFT JOIN exams   orig_e ON orig_e.exam_id   = e.cloned_from_exam_id
               LEFT JOIN users   orig_u ON orig_u.user_id   = orig_e.created_by
               LEFT JOIN schools orig_s ON orig_s.school_id = orig_e.school_id" : "");

if ($view === 'mine') {
    // Mine = my creations + my school's. Co-teachers are joint owners.
    $params = [];
    $types  = '';

    if ($acl['is_developer']) {
        $sql = "SELECT $select_cols
                  FROM exams e
                  $base_joins
                 WHERE e.created_by = ?
                 ORDER BY e.created_at DESC LIMIT 200";
        $types  .= 'i';
        $params[] = $user_id;
    } else {
        $sql = "SELECT $select_cols
                  FROM exams e
                  $base_joins
                 WHERE e.created_by = ?
                    OR (? > 0 AND e.school_id = ?)
                 ORDER BY e.created_at DESC LIMIT 200";
        $types  .= 'iii';
        $params[] = $user_id;
        $params[] = $school_id;
        $params[] = $school_id;
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
} else {
    // Public = is_public = 1, regardless of who the creator is. The owner
    // SEES their own exam in Public the moment they toggle is_public=ON —
    // they explicitly asked for that confirmation. Same-school exams that
    // aren't flagged Public still live exclusively under "My Exams".
    $params = [];
    $types  = '';

    $where = "WHERE e.is_public = 1";

    if ($search !== '') {
        // Search title / topic / grade / creator name / school name.
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
             ORDER BY e.created_at DESC LIMIT 200";
    $stmt = $conn->prepare($sql);
    // bind_param errors on empty types — only call it when we actually have
    // parameters (i.e. the search is non-empty).
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Management Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        .header h1 {
            color: #333;
            font-size: 28px;
        }

        .header a {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }

        .header a:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
        }

        .exams-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .exam-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }

        .exam-item:hover {
            background: #f8f9ff;
        }

        .exam-info h3 {
            margin-bottom: 8px;
            color: #333;
        }

        .exam-info p {
            font-size: 13px;
            color: #999;
            margin: 5px 0;
        }

        .exam-meta {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge.draft {
            background: #fff3cd;
            color: #856404;
        }

        .badge.active {
            background: #d4edda;
            color: #155724;
        }

        .badge.ended {
            background: #f8d7da;
            color: #721c24;
        }

        .exam-code {
            font-family: 'Courier New', monospace;
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #333;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-activate {
            background: #28a745;
            color: white;
        }

        .btn-activate:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-deactivate {
            background: #dc3545;
            color: white;
        }

        .btn-deactivate:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-view {
            background: #667eea;
            color: white;
            text-decoration: none;
        }

        .btn-view:hover {
            background: #764ba2;
        }

        .empty {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty h2 {
            margin-bottom: 10px;
            color: #666;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1000;
        }

        .toast.show {
            opacity: 1;
        }

        .toast.success {
            background: #28a745;
        }

        .toast.error {
            background: #dc3545;
        }

        .time-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
    <link rel="stylesheet" href="<?= APP_BASE_URL ?>/exams/assets/exam-theme.css">
</head>
<body class="exam-dark">
    <div class="container">
        <a href="<?= APP_BASE_URL ?>/Auth/SF/index.php"
           style="display:inline-flex;align-items:center;gap:8px;margin-bottom:14px;padding:10px 18px;background:rgba(255,255,255,.08);color:#fff;font-weight:700;font-size:13px;border-radius:8px;text-decoration:none;border:1px solid rgba(168,85,247,.3)">
            ← Back to LMS Home
        </a>
        <div class="header">
            <h1 style="color:#fff">
                📚 <?= $view === 'public' ? 'Public Library' : 'My Exams' ?>
            </h1>
            <a href="exam_creator_working.php" class="create-btn" style="color:#fff;font-weight:800;background:linear-gradient(135deg,#7c3aed,#a855f7);padding:12px 24px;border-radius:8px;text-decoration:none;box-shadow:0 8px 24px rgba(124,58,237,.4)">+ Create New Exam</a>
        </div>

        <!-- Tab switcher: My Exams (default) vs Public Library.
             Public lists same-school + globally-public exams so you can
             discover exams from other teachers without losing same-school
             collab visibility. -->
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
                margin-left:8px;
            }
            .origin-badge {
                display:inline-block;
                padding:3px 9px; border-radius:999px;
                font-size:11px; font-weight:800; letter-spacing:.4px;
                text-transform:uppercase;
            }
            .origin-school { background:rgba(59,130,246,.18); color:#93c5fd; border:1px solid rgba(59,130,246,.4); }
            .origin-public { background:rgba(16,185,129,.18); color:#6ee7b7; border:1px solid rgba(16,185,129,.4); }
            .pub-toggle {
                cursor:pointer; border:none;
                padding:6px 12px; border-radius:999px;
                font-weight:800; font-size:11px; letter-spacing:.4px;
                text-transform:uppercase;
            }
            .pub-toggle.on  { background:rgba(16,185,129,.18); color:#6ee7b7; border:1px solid rgba(16,185,129,.4); }
            .pub-toggle.off { background:rgba(148,163,184,.18); color:#cbd5e1; border:1px solid rgba(148,163,184,.4); }
            .pub-toggle:hover { transform:translateY(-1px); }
            .pub-toggle:disabled { opacity:.55; cursor:wait; }

            .adapted-chip {
                display:inline-flex; align-items:center; gap:6px;
                padding:3px 10px; border-radius:999px;
                background:rgba(245,158,11,.16); color:#fde68a;
                font-size:11px; font-weight:800; letter-spacing:.3px;
                border:1px solid rgba(245,158,11,.4);
                margin-left:8px;
            }
            .preview-btn  {
                padding:10px 18px; border-radius:8px; border:1px solid rgba(59,130,246,.45);
                background:rgba(59,130,246,.18); color:#93c5fd;
                font-weight:800; font-size:12px; cursor:pointer;
                text-decoration:none;
            }
            .preview-btn:hover { background:rgba(59,130,246,.28); }
            .republish-card-btn {
                padding:10px 18px; border-radius:8px; border:none;
                background:linear-gradient(135deg,#10b981,#22c55e); color:#fff;
                font-weight:800; font-size:12px; cursor:pointer;
                box-shadow:0 6px 16px rgba(16,185,129,.3);
            }
            .republish-card-btn:hover { transform:translateY(-1px); }
        </style>

        <div class="stats">
            <?php
            $draft_count = count(array_filter($exams, fn($e) => $e['status'] === 'draft'));
            $active_count = count(array_filter($exams, fn($e) => $e['status'] === 'active'));
            $ended_count = count(array_filter($exams, fn($e) => $e['status'] === 'ended'));
            $total_count = count($exams);
            ?>
            <div class="stat-card">
                <h3>Total Exams</h3>
                <div class="number"><?= $total_count ?></div>
            </div>
            <div class="stat-card">
                <h3>Draft</h3>
                <div class="number" style="color: #ffc107"><?= $draft_count ?></div>
            </div>
            <div class="stat-card">
                <h3>Active</h3>
                <div class="number" style="color: #28a745"><?= $active_count ?></div>
            </div>
            <div class="stat-card">
                <h3>Ended</h3>
                <div class="number" style="color: #dc3545"><?= $ended_count ?></div>
            </div>
        </div>

        <div class="exams-list">
            <?php if (empty($exams)): ?>
                <div class="empty">
                    <?php if ($view === 'public'): ?>
                        <h2>Nothing in the Public Library yet</h2>
                        <p>
                            <?= $search !== ''
                                ? 'No public exam matches "' . htmlspecialchars($search) . '". Try a different keyword.'
                                : 'When a teacher marks their exam Public, it will show up here.' ?>
                        </p>
                    <?php else: ?>
                        <h2>No Exams Yet</h2>
                        <p>Create your first exam to get started, or
                           <a href="?view=public" style="color:#a78bfa;font-weight:700;text-decoration:underline;">browse the Public Library</a>.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($exams as $exam):
                    $is_actual_creator = ((int)$exam['created_by'] === (int)$acl['user_id']);
                    $viewer_school = (int)($acl['school_id'] ?? 0);
                    $exam_school   = (int)($exam['school_id'] ?? 0);
                    // Co-owner: same-school teammate. Treated as joint owner
                    // under the widened ACL — can edit, view answer keys, toggle
                    // Public — everything except delete.
                    $is_co_owner   = !$is_actual_creator
                                  && $viewer_school > 0 && $exam_school > 0
                                  && $viewer_school === $exam_school;

                    // Anyone who can "own" (creator OR co-owner OR developer).
                    $is_owner  = $is_actual_creator || $is_co_owner || $acl['is_developer'];
                    // Anyone who can collaborate (activate, deactivate, republish,
                    // view submissions). Currently the same set as owner under
                    // the new model.
                    $is_collab = $is_owner;

                    $is_pub        = (int)($exam['is_public'] ?? 0) === 1;
                    $creator_name  = trim((string)($exam['owner_name'] ?? '')) ?: 'colleague';
                    $school_name   = trim((string)($exam['school_name'] ?? ''));

                    // Adapted-from credit (if this row is a republished clone).
                    $adapted_name   = trim((string)($exam['adapted_from_name']   ?? ''));
                    $adapted_school = trim((string)($exam['adapted_from_school'] ?? ''));
                    $is_adapted     = $adapted_name !== '';
                ?>
                    <div class="exam-item">
                        <div class="exam-info">
                            <h3>
                                <?= htmlspecialchars($exam['title']) ?>
                                <?php if ($view === 'public'): ?>
                                    <span class="origin-badge origin-public">🌐 Public</span>
                                    <span class="credit-chip">
                                        🧑‍🏫 By: <?= htmlspecialchars($creator_name) ?><?= $school_name !== '' ? ' @ ' . htmlspecialchars($school_name) : '' ?>
                                    </span>
                                <?php elseif ($is_co_owner): ?>
                                    <span class="origin-badge origin-school">🏫 Same school</span>
                                    <span class="credit-chip">
                                        🧑‍🏫 By: <?= htmlspecialchars($creator_name) ?>
                                    </span>
                                <?php elseif ($is_actual_creator && $is_pub): ?>
                                    <span class="origin-badge origin-public">🌐 In public library</span>
                                <?php endif; ?>

                                <?php if ($is_adapted): ?>
                                    <span class="adapted-chip">
                                        📝 Adapted from <?= htmlspecialchars($adapted_name) ?><?= $adapted_school !== '' ? ' @ ' . htmlspecialchars($adapted_school) : '' ?>
                                    </span>
                                <?php endif; ?>
                            </h3>
                            <p>📝 Topic: <strong><?= htmlspecialchars($exam['topic']) ?></strong> | 👥 Grade: <strong><?= htmlspecialchars($exam['grade']) ?></strong></p>
                            <p>📅 Created: <?= date('M d, Y H:i', strtotime($exam['created_at'])) ?></p>
                            <?php if ($exam['status'] === 'active' && $view !== 'public'): ?>
                                <div class="time-info">⏱️ Start Time: <?= date('M d, Y H:i', strtotime($exam['start_time'])) ?></div>
                            <?php endif; ?>

                            <?php if ($view !== 'public' && $is_owner): ?>
                                <!-- Owner / co-owner can opt this exam in/out of the global Public Library. -->
                                <p style="margin-top:10px;">
                                    <button type="button"
                                            class="pub-toggle <?= $is_pub ? 'on' : 'off' ?>"
                                            data-exam="<?= (int)$exam['exam_id'] ?>"
                                            data-value="<?= $is_pub ? 1 : 0 ?>"
                                            title="When ON, teachers at other schools can find this exam in the Public Library. The original author's name stays credited.">
                                        🌐 Public: <strong class="pub-state"><?= $is_pub ? 'ON' : 'OFF' ?></strong>
                                    </button>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="exam-meta">
                            <span class="exam-code">Code: <?= $exam['exam_code'] ?></span>
                            <span class="badge <?= strtolower($exam['status']) ?>"><?= ucfirst($exam['status']) ?></span>
                            <div class="actions">
                                <?php if ($view === 'public'): ?>
                                    <!-- Cross-school exam — preview, then republish. -->
                                    <a href="preview_public_exam.php?exam_id=<?= (int)$exam['exam_id'] ?>" class="preview-btn">👁 Preview</a>
                                    <button type="button" class="republish-card-btn"
                                            onclick="republishToMySchool(<?= (int)$exam['exam_id'] ?>)">
                                        📥 Republish to my school
                                    </button>
                                <?php else: ?>
                                    <?php if ($is_collab): ?>
                                        <?php if ($exam['status'] === 'draft'): ?>
                                            <button class="btn btn-activate" onclick="activateExam(<?= $exam['exam_id'] ?>)">Activate</button>
                                        <?php elseif ($exam['status'] === 'active'): ?>
                                            <button class="btn btn-deactivate" onclick="deactivateExam(<?= $exam['exam_id'] ?>)">Deactivate</button>
                                        <?php endif; ?>
                                        <a href="assignment_submissions.php?exam_id=<?= $exam['exam_id'] ?>" class="btn btn-view" style="background:#7c3aed;">📎 Submissions</a>
                                        <button class="btn btn-view" style="background:#f59e0b;" onclick="republishExam(<?= $exam['exam_id'] ?>, '<?= htmlspecialchars(addslashes($exam['title'])) ?>')">🔄 Re-Publish</button>
                                    <?php endif; ?>
                                    <?php if ($is_owner): ?>
                                        <!-- Edit + View expose answer keys / rewrite content → owner / co-owner / dev only -->
                                        <a href="view_exam.php?exam_id=<?= $exam['exam_id'] ?>" class="btn btn-view">📋 View</a>
                                        <a href="exam_creator_working.php?exam_id=<?= $exam['exam_id'] ?>" class="btn btn-view" style="background:#28a745;">✏️ Edit</a>
                                    <?php endif; ?>
                                    <a href="exam_report.php?exam_id=<?= $exam['exam_id'] ?>" class="btn btn-view" style="background:#2563eb;">📈 Reports</a>
                                    <a href="question_report.php?exam_id=<?= $exam['exam_id'] ?>" class="btn btn-view" style="background:#4f46e5;">📊 Per-question</a>
                                    <?php if ($is_actual_creator): ?>
                                        <!-- Delete is the original creator's call only (co-teachers can edit, not delete). -->
                                        <button class="btn" style="background:#dc3545;color:#fff;"
                                                onclick="deleteExam(<?= (int)$exam['exam_id'] ?>, '<?= htmlspecialchars(addslashes($exam['title'])) ?>')">🗑️ Delete</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        async function deleteExam(examId, title) {
            if (!confirm('Delete "' + title + '" and ALL its questions, options and results?\n\nThis cannot be undone.')) return;

            try {
                const response = await fetch('delete_exam.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ exam_id: examId })
                });
                const data = await response.json();
                if (data.success) {
                    showToast('🗑️ Exam deleted', 'success');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showToast('❌ ' + (data.error || 'Could not delete exam'), 'error');
                }
            } catch (err) {
                showToast('❌ Error: ' + err.message, 'error');
            }
        }

        async function activateExam(examId) {
            if (!confirm('Activate this exam and start it immediately?')) return;
            
            try {
                const response = await fetch('activate_exam.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        exam_id: examId,
                        action: 'activate',
                        start_now: true
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showToast('✓ Exam activated!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('❌ ' + data.error, 'error');
                }
            } catch (err) {
                showToast('❌ Error: ' + err.message, 'error');
            }
        }

        async function deactivateExam(examId) {
            if (!confirm('Deactivate this exam?')) return;
            
            try {
                const response = await fetch('activate_exam.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        exam_id: examId,
                        action: 'deactivate'
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showToast('✓ Exam deactivated!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('❌ ' + data.error, 'error');
                }
            } catch (err) {
                showToast('❌ Error: ' + err.message, 'error');
            }
        }

        function showToast(msg, type = 'info') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = 'toast ' + type;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
        async function republishExam(examId, examTitle) {
    const label = prompt(
        `📚 Re-publish "${examTitle}"\n\n` +
        `Enter a label for this new session (e.g. "Class 6A", "Class 6B"):`
    );
    
    if (!label || !label.trim()) return;

    try {
        const formData = new FormData();
        formData.append('exam_id', examId);
        formData.append('label', label.trim());

        const response = await fetch('republish_exam.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert(
                `✅ New session created for "${examTitle}"!\n\n` +
                `📋 Session: ${data.label}\n` +
                `🔑 New Code: ${data.code}\n` +
                `🔐 Pin: ${data.pin}\n\n` +
                `Share the code with your students!`
            );
            location.reload();
        } else {
            alert('❌ Error: ' + data.error);
        }
    } catch (err) {
        alert('❌ Error: ' + err.message);
    }
}

// Per-exam Public toggle. Mirrors the perm-pill flow from exam_report.php so
// owners get instant feedback and a clear ON/OFF state, with a graceful
// fallback to the previous state on error.
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
            const r = await fetch('exam_public_toggle.php', { method: 'POST', body: fd });
            const j = await r.json();
            if (!j.success) throw new Error(j.error || 'save failed');
            btn.dataset.value = next;
            btn.classList.toggle('on',  next === 1);
            btn.classList.toggle('off', next === 0);
            if (stateEl) stateEl.textContent = next ? 'ON' : 'OFF';
            showToast(next ? '🌐 Now visible in Public Library' : '🔒 Removed from Public Library', 'success');
        } catch (err) {
            showToast('❌ ' + err.message, 'error');
            if (stateEl) stateEl.textContent = current ? 'ON' : 'OFF';
        } finally {
            btn.disabled = false;
        }
    });
});

// Public-Library cards: republish a colleague's exam into my school.
async function republishToMySchool(examId) {
    if (!confirm('📥 Republish this exam to your school?\n\nA brand-new copy will be added to My Exams. Your students start on a fresh leaderboard.')) return;
    try {
        const fd = new FormData();
        fd.append('exam_id', examId);
        const r = await fetch('clone_exam.php', { method: 'POST', body: fd });
        const j = await r.json();
        if (!j.success) throw new Error(j.error || 'clone failed');
        showToast('✅ Republished — new code: ' + j.new_exam_code, 'success');
        setTimeout(() => { window.location.href = '?view=mine'; }, 1200);
    } catch (err) {
        showToast('❌ ' + err.message, 'error');
    }
}
    </script>
</body>
</html>
