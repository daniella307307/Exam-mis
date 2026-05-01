<?php
/**
 * Live Online Classes — lightweight bulletin board.
 *
 * Teachers post the title + Google Meet (or other) URL + start time.
 * Other teachers see upcoming/live sessions and click to join.
 * The host can cancel their own session; admin (access_level = 1) can
 * cancel anyone's. Status (Scheduled / Live / Ended) is derived from
 * scheduled_at + duration so we never lie about it.
 *
 * Migration: db_backup/live_classes_migration.sql
 */
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../Auth/auth_helpers.php';

// ---- Auth gate -------------------------------------------------------------
if (empty($_SESSION['user_id'])) {
    header('Location: /Exam-mis/Administrator_login.php');
    exit;
}
$current_user_id = (int)$_SESSION['user_id'];
$is_admin        = (int)($_SESSION['access_level'] ?? 0) === 1;

// ---- POST actions ----------------------------------------------------------
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!auth_csrf_check($_POST['csrf_token'] ?? null)) {
        $flash = ['type' => 'error', 'msg' => 'Your session expired. Please refresh and try again.'];
    } else {
        $action = (string)($_POST['action'] ?? '');

        if ($action === 'create') {
            $title       = trim((string)($_POST['title'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $meet_link   = trim((string)($_POST['meet_link'] ?? ''));
            $scheduled   = trim((string)($_POST['scheduled_at'] ?? ''));
            $duration    = (int)($_POST['duration_min'] ?? 60);
            $audience    = trim((string)($_POST['audience'] ?? 'teachers'));

            if ($title === '' || $meet_link === '' || $scheduled === '') {
                $flash = ['type' => 'error', 'msg' => 'Title, meet link and start time are required.'];
            } elseif (mb_strlen($title) > 180) {
                $flash = ['type' => 'error', 'msg' => 'Title is too long (max 180 characters).'];
            } elseif (!filter_var($meet_link, FILTER_VALIDATE_URL) || !str_starts_with(strtolower($meet_link), 'https://')) {
                $flash = ['type' => 'error', 'msg' => 'Meet link must be a valid https:// URL (e.g. https://meet.google.com/abc-defg-hij).'];
            } elseif (mb_strlen($meet_link) > 500) {
                $flash = ['type' => 'error', 'msg' => 'Meet link is too long (max 500 characters).'];
            } else {
                $ts = strtotime($scheduled);
                if ($ts === false) {
                    $flash = ['type' => 'error', 'msg' => 'Could not parse the start time.'];
                } else {
                    if ($duration < 5)   { $duration = 5; }
                    if ($duration > 480) { $duration = 480; }
                    $scheduled_at = date('Y-m-d H:i:s', $ts);

                    try {
                        $stmt = $conn->prepare(
                            "INSERT INTO live_classes
                                (host_user_id, title, description, meet_link, scheduled_at, duration_min, audience)
                             VALUES (?, ?, ?, ?, ?, ?, ?)"
                        );
                        $stmt->bind_param(
                            'issssis',
                            $current_user_id,
                            $title,
                            $description,
                            $meet_link,
                            $scheduled_at,
                            $duration,
                            $audience
                        );
                        $stmt->execute();
                        $stmt->close();
                        $flash = ['type' => 'success', 'msg' => 'Live class scheduled. Other teachers can see it now.'];
                    } catch (Throwable $e) {
                        error_log('live_classes create failed: ' . $e->getMessage());
                        $flash = ['type' => 'error', 'msg' => 'Could not save the class. Please try again.'];
                    }
                }
            }
        } elseif ($action === 'cancel') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                try {
                    if ($is_admin) {
                        $stmt = $conn->prepare("UPDATE live_classes SET is_cancelled = 1 WHERE id = ?");
                        $stmt->bind_param('i', $id);
                    } else {
                        $stmt = $conn->prepare("UPDATE live_classes SET is_cancelled = 1 WHERE id = ? AND host_user_id = ?");
                        $stmt->bind_param('ii', $id, $current_user_id);
                    }
                    $stmt->execute();
                    $stmt->close();
                    $flash = ['type' => 'success', 'msg' => 'Class cancelled.'];
                } catch (Throwable $e) {
                    error_log('live_classes cancel failed: ' . $e->getMessage());
                    $flash = ['type' => 'error', 'msg' => 'Could not cancel the class. Please try again.'];
                }
            }
        }
    }

    // PRG to keep the form fresh after submit.
    header('Location: live_classes.php' . ($flash ? ('?m=' . urlencode($flash['type']) . '&t=' . urlencode($flash['msg'])) : ''));
    exit;
}

// Read flash from PRG redirect.
if (!$flash && isset($_GET['m'], $_GET['t'])) {
    $type = (string)$_GET['m'];
    if (in_array($type, ['error', 'success'], true)) {
        $flash = ['type' => $type, 'msg' => substr((string)$_GET['t'], 0, 240)];
    }
}

// ---- Fetch classes ---------------------------------------------------------
$classes   = [];
$fetch_err = null;
$sql = "SELECT lc.id, lc.host_user_id, lc.title, lc.description, lc.meet_link,
               lc.scheduled_at, lc.duration_min, lc.audience, lc.is_cancelled, lc.created_at,
               u.firstname, u.lastname
          FROM live_classes lc
     LEFT JOIN users u ON u.user_id = lc.host_user_id
         WHERE (lc.is_cancelled = 0
                AND lc.scheduled_at >= DATE_SUB(NOW(), INTERVAL 1 DAY))
            OR (lc.host_user_id = ? AND lc.scheduled_at >= DATE_SUB(NOW(), INTERVAL 7 DAY))
      ORDER BY lc.scheduled_at ASC
         LIMIT 200";
try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $current_user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $classes[] = $r; }
    $stmt->close();
} catch (Throwable $e) {
    error_log('live_classes fetch failed: ' . $e->getMessage());
    if (stripos($e->getMessage(), "doesn't exist") !== false) {
        $fetch_err = 'The live_classes table is missing. Run db_backup/live_classes_migration.sql then refresh.';
    } else {
        $fetch_err = 'Could not load live classes right now. Please refresh in a moment.';
    }
}

/** Returns ['Live'|'Scheduled'|'Ended'|'Cancelled', minutes_remaining_or_zero]. */
function lc_status(array $c): array {
    if ((int)$c['is_cancelled'] === 1) {
        return ['Cancelled', 0];
    }
    $start = strtotime($c['scheduled_at']);
    $end   = $start + ((int)$c['duration_min'] * 60);
    $now   = time();
    if ($now < $start) {
        return ['Scheduled', (int)ceil(($start - $now) / 60)];
    }
    if ($now <= $end) {
        return ['Live', (int)ceil(($end - $now) / 60)];
    }
    return ['Ended', 0];
}

$csrf = auth_csrf_token();
$default_dt = date('Y-m-d\TH:i', strtotime('+30 minutes'));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Live Online Classes | BLIS LMS</title>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css">
<link rel="stylesheet" href="/Exam-mis/exams/assets/exam-theme.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Nunito','Segoe UI',sans-serif;padding:24px;color:#f1f5f9}
.wrap{max-width:1100px;margin:0 auto;position:relative;z-index:1}
.back{display:inline-flex;align-items:center;gap:8px;margin-bottom:14px;padding:10px 18px;
      background:rgba(255,255,255,.08);color:#fff;font-weight:700;font-size:13px;
      border-radius:8px;text-decoration:none;border:1px solid rgba(168,85,247,.3)}
.page-title{font-size:28px;font-weight:900;margin-bottom:4px;color:#fff}
.subtitle{color:#cbd5e1;margin-bottom:24px}
.alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:700}
.alert.error{background:rgba(239,68,68,.18);color:#fca5a5;border:1px solid rgba(239,68,68,.4)}
.alert.success{background:rgba(34,197,94,.18);color:#86efac;border:1px solid rgba(34,197,94,.4)}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media (max-width:900px){.grid{grid-template-columns:1fr}}
.card{background:rgba(255,255,255,.05);border:1px solid rgba(168,85,247,.3);
      border-radius:18px;padding:22px;backdrop-filter:blur(20px)}
.card h2{color:#fff;font-size:18px;margin-bottom:14px}
label{display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;
      color:#cbd5e1;margin-bottom:6px;margin-top:12px}
input,textarea,select{width:100%;background:rgba(15,15,40,.6);border:1.5px solid rgba(168,85,247,.25);
      border-radius:10px;padding:11px 14px;color:#f1f5f9;font-family:inherit;font-size:14px;font-weight:600;outline:none;color-scheme:dark}
input:focus,textarea:focus,select:focus{border-color:#a855f7;background:rgba(15,15,40,.85)}
/* Make the native date/time picker icon readable on dark glass.
   Replaces the OS-rendered icon (which was being double-inverted by
   color-scheme:dark + filter:invert and ended up black) with an
   explicit white SVG so it's the same on every browser. */
input[type="datetime-local"]::-webkit-calendar-picker-indicator,
input[type="date"]::-webkit-calendar-picker-indicator,
input[type="time"]::-webkit-calendar-picker-indicator{
    cursor:pointer;
    opacity:1;
    width:20px;height:20px;padding:2px;
    background-color:transparent;
    background-repeat:no-repeat;
    background-position:center;
    background-size:18px 18px;
    background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='3' y='4' width='18' height='18' rx='2' ry='2'/><line x1='16' y1='2' x2='16' y2='6'/><line x1='8' y1='2' x2='8' y2='6'/><line x1='3' y1='10' x2='21' y2='10'/></svg>");
}
input[type="datetime-local"]::-webkit-calendar-picker-indicator:hover,
input[type="date"]::-webkit-calendar-picker-indicator:hover,
input[type="time"]::-webkit-calendar-picker-indicator:hover{
    background-color:rgba(168,85,247,.25);
    border-radius:4px;
}
textarea{min-height:78px;resize:vertical}
.row{display:flex;gap:12px}
.row > *{flex:1}
.btn-primary{display:inline-block;width:100%;margin-top:16px;padding:13px;
      background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;font-weight:800;
      border:none;border-radius:10px;cursor:pointer;font-size:14px;
      box-shadow:0 8px 24px rgba(124,58,237,.4);font-family:inherit}
.btn-primary:hover{transform:translateY(-1px)}
.list{display:flex;flex-direction:column;gap:14px;margin-top:14px}
.session{background:rgba(255,255,255,.04);border:1px solid rgba(168,85,247,.22);
      border-radius:14px;padding:16px}
.session.live{border-color:rgba(34,197,94,.5);box-shadow:0 0 0 2px rgba(34,197,94,.18) inset}
.session.cancelled{opacity:.55}
.session-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
.session-title{font-size:16px;font-weight:800;color:#fff}
.session-host{font-size:12px;color:#cbd5e1;margin-top:2px}
.pill{display:inline-block;padding:4px 10px;border-radius:99px;font-size:10px;font-weight:800;letter-spacing:1px;text-transform:uppercase}
.pill.live{background:rgba(34,197,94,.18);color:#86efac;border:1px solid rgba(34,197,94,.4)}
.pill.scheduled{background:rgba(124,58,237,.2);color:#c4b5fd;border:1px solid rgba(124,58,237,.4)}
.pill.ended{background:rgba(148,163,184,.15);color:#cbd5e1;border:1px solid rgba(148,163,184,.3)}
.pill.cancelled{background:rgba(239,68,68,.18);color:#fca5a5;border:1px solid rgba(239,68,68,.4)}
.session-meta{display:flex;gap:14px;margin-top:10px;font-size:12px;color:#cbd5e1;flex-wrap:wrap}
.session-meta span i{color:#a855f7;margin-right:4px}
.session-desc{margin-top:10px;color:#e2e8f0;font-size:13px;line-height:1.5;white-space:pre-wrap;word-break:break-word}
.session-actions{display:flex;gap:8px;margin-top:14px;flex-wrap:wrap}
.session-actions a.join{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;
      background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;font-weight:800;font-size:13px;
      text-decoration:none;border-radius:8px;box-shadow:0 6px 18px rgba(124,58,237,.35)}
.session-actions a.join.disabled{background:rgba(148,163,184,.2);color:#cbd5e1;
      box-shadow:none;pointer-events:none}
.session-actions form{margin:0}
.session-actions button.cancel{padding:9px 14px;background:rgba(239,68,68,.18);color:#fca5a5;
      font-weight:700;font-size:12px;border:1px solid rgba(239,68,68,.4);border-radius:8px;cursor:pointer;font-family:inherit}
.session-actions button.cancel:hover{background:rgba(239,68,68,.3)}
.empty{padding:40px;text-align:center;color:#cbd5e1}
</style>
</head>
<body class="exam-dark">
<div class="wrap">
    <a href="/Exam-mis/Auth/SF/index.php" class="back">← Back to LMS Home</a>
    <h1 class="page-title">🎥 Live Online Classes</h1>
    <p class="subtitle">Schedule a meet, or join one already in progress.</p>

    <?php if ($flash): ?>
        <div class="alert <?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <?php if ($fetch_err): ?>
        <div class="alert error">
            <?= htmlspecialchars($fetch_err, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="grid">

        <!-- LIST -->
        <div class="card" style="grid-column:1 / span 2">
            <h2>Upcoming &amp; live classes</h2>
            <?php if (empty($classes)): ?>
                <div class="empty">No live classes scheduled yet. Be the first to share a meet link.</div>
            <?php else: ?>
                <div class="list">
                <?php foreach ($classes as $c): ?>
                    <?php
                        [$status, $rem] = lc_status($c);
                        $pill_cls = strtolower($status);
                        $session_cls = $pill_cls;
                        $is_owner = ((int)$c['host_user_id'] === $current_user_id);
                        $can_join = ($status === 'Live' || $status === 'Scheduled') && (int)$c['is_cancelled'] === 0;
                        $when     = date('D, M j · H:i', strtotime($c['scheduled_at']));
                        $hostname = trim((string)$c['firstname'] . ' ' . (string)$c['lastname']);
                        if ($hostname === '') { $hostname = 'Teacher #' . (int)$c['host_user_id']; }
                    ?>
                    <div class="session <?= htmlspecialchars($session_cls, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="session-head">
                            <div>
                                <div class="session-title"><?= htmlspecialchars($c['title'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="session-host">Hosted by <?= htmlspecialchars($hostname, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                            <span class="pill <?= htmlspecialchars($pill_cls, ENT_QUOTES, 'UTF-8') ?>">
                                <?php
                                    if ($status === 'Live')         echo '🔴 Live · ' . (int)$rem . 'm left';
                                    elseif ($status === 'Scheduled') echo 'Starts in ' . (int)$rem . 'm';
                                    else                              echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
                                ?>
                            </span>
                        </div>
                        <div class="session-meta">
                            <span><i class="far fa-calendar"></i><?= htmlspecialchars($when, ENT_QUOTES, 'UTF-8') ?></span>
                            <span><i class="far fa-clock"></i><?= (int)$c['duration_min'] ?> min</span>
                            <span><i class="fas fa-users"></i><?= htmlspecialchars(ucfirst((string)$c['audience']), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <?php if (!empty($c['description'])): ?>
                            <div class="session-desc"><?= htmlspecialchars($c['description'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <div class="session-actions">
                            <?php if ($can_join): ?>
                                <a class="join" target="_blank" rel="noopener noreferrer"
                                   href="<?= htmlspecialchars($c['meet_link'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fas fa-video"></i> Join meeting
                                </a>
                            <?php else: ?>
                                <span class="join disabled"><i class="fas fa-video"></i> Join meeting</span>
                            <?php endif; ?>
                            <?php if (($is_owner || $is_admin) && (int)$c['is_cancelled'] === 0 && $status !== 'Ended'): ?>
                                <form method="POST" onsubmit="return confirm('Cancel this class?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <button type="submit" class="cancel">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- CREATE -->
        <div class="card" style="grid-column:1 / span 2">
            <h2>Schedule a new live class</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="create">

                <label for="title">Title *</label>
                <input id="title" name="title" type="text" maxlength="180" required placeholder="e.g. Robotics Q&amp;A session">

                <div class="row">
                    <div>
                        <label for="scheduled_at">Start time *</label>
                        <input id="scheduled_at" name="scheduled_at" type="datetime-local" required value="<?= htmlspecialchars($default_dt, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="duration_min">Duration (minutes)</label>
                        <input id="duration_min" name="duration_min" type="number" min="5" max="480" value="60" required>
                    </div>
                </div>

                <label for="meet_link">Meet link (Google Meet, Zoom, Teams) *</label>
                <input id="meet_link" name="meet_link" type="url" required maxlength="500" placeholder="https://meet.google.com/abc-defg-hij">

                <label for="audience">Audience</label>
                <select id="audience" name="audience">
                    <option value="teachers">Teachers</option>
                    <option value="students">Students</option>
                    <option value="all">All</option>
                </select>

                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" maxlength="2000" placeholder="Agenda, prerequisites, links…"></textarea>

                <button class="btn-primary" type="submit">📅 Schedule class</button>
            </form>
        </div>

    </div>
</div>
</body>
</html>
