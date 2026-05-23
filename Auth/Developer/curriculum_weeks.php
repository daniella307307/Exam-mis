<?php
/**
 * Developer curriculum manager — Week cards for a month.
 * URL: curriculum_weeks.php?CERT=<id>&TERM=<1-3>&MONTH=<1-12>
 */
include('header.php');
require_once(__DIR__ . '/../curriculum_helpers.php');

curriculum_ensure_table($conn);

$CERT  = isset($_GET['CERT'])  ? (int)$_GET['CERT']  : 0;
$TERM  = isset($_GET['TERM'])  ? (int)$_GET['TERM']  : 0;
$MONTH = isset($_GET['MONTH']) ? (int)$_GET['MONTH'] : 0;

$terms = curriculum_terms();
if ($CERT <= 0 || !isset($terms[$TERM]) || !in_array($MONTH, $terms[$TERM]['months'], true)) {
    echo '<p class="p-4">Bad term/month/cert combo. <a href="Curriculum.php" class="text-blue-600 underline">Back</a>.</p>';
    include('footer.php'); exit;
}

$cstmt = $conn->prepare("SELECT certification_id, certification_name FROM certifications WHERE certification_id = ? LIMIT 1");
$cstmt->bind_param('i', $CERT);
$cstmt->execute();
$cert = $cstmt->get_result()->fetch_assoc();
$cstmt->close();
if (!$cert) { echo '<p class="p-4">Certification not found.</p>'; include('footer.php'); exit; }

$rows = [];
$wstmt = $conn->prepare(
    "SELECT week_number, title, bunny_pdf_url, bunny_video_url
       FROM curriculum_weeks
      WHERE certification_id = ? AND term_number = ? AND month_number = ?"
);
$wstmt->bind_param('iii', $CERT, $TERM, $MONTH);
$wstmt->execute();
$wres = $wstmt->get_result();
while ($r = $wres->fetch_assoc()) { $rows[(int)$r['week_number']] = $r; }
$wstmt->close();

$names      = curriculum_month_names();
$week_count = curriculum_default_week_count($MONTH);
$is_proj    = curriculum_is_projects_month($MONTH);
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">
        <div class="flex items-center gap-3 mb-3">
            <a href="curriculum_months.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$TERM ?>" class="back-btn">← Back to <?= htmlspecialchars($terms[$TERM]['label']) ?> months</a>
        </div>
        <div class="text-xs text-gray-500 mb-2 uppercase tracking-wide font-semibold">
            <a href="Curriculum.php" class="hover:text-purple-600">Curriculum</a>
            <span class="mx-1">›</span>
            <a href="curriculum_terms.php?CERT=<?= (int)$CERT ?>" class="hover:text-purple-600"><?= htmlspecialchars($cert['certification_name']) ?></a>
            <span class="mx-1">›</span>
            <a href="curriculum_months.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$TERM ?>" class="hover:text-purple-600"><?= htmlspecialchars($terms[$TERM]['label']) ?></a>
            <span class="mx-1">›</span>
            <span class="text-gray-800"><?= htmlspecialchars($names[$MONTH]) ?></span>
        </div>

        <div class="flex justify-between items-end mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    <?= htmlspecialchars($names[$MONTH]) ?>
                    <span class="text-base font-medium text-gray-500">· <?= htmlspecialchars($terms[$TERM]['label']) ?></span>
                </h1>
                <p class="text-sm text-gray-500">
                    <?= $is_proj
                        ? '🎯 Projects month — wrap-up week included.'
                        : 'Standard 4-week month.' ?>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php for ($w = 1; $w <= $week_count; $w++):
                $row     = $rows[$w] ?? null;
                $title   = $row['title']           ?? '';
                $has_pdf = !empty($row['bunny_pdf_url']);
                $has_vid = !empty($row['bunny_video_url']);
                $ready   = $has_pdf || $has_vid;
            ?>
                <a href="curriculum_week.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$TERM ?>&MONTH=<?= (int)$MONTH ?>&WEEK=<?= $w ?>"
                   class="week-card<?= $ready ? ' ready' : '' ?>">
                    <div class="week-card-inner">
                        <div class="week-head">
                            <div>
                                <div class="week-num">Week <?= $w ?></div>
                                <?php if ($title !== ''): ?>
                                    <div class="week-title"><?= htmlspecialchars($title) ?></div>
                                <?php else: ?>
                                    <div class="week-title text-gray-400 italic">(no title yet)</div>
                                <?php endif; ?>
                            </div>
                            <?php if ($is_proj && $w === $week_count): ?>
                                <span class="proj-badge">🎯 Project wrap</span>
                            <?php endif; ?>
                        </div>
                        <div class="week-meta">
                            <span class="<?= $has_pdf ? 'meta-on' : 'meta-off' ?>">📄 PDF <?= $has_pdf ? '✓' : '—' ?></span>
                            <span class="<?= $has_vid ? 'meta-on' : 'meta-off' ?>">🎬 Video <?= $has_vid ? '✓' : '—' ?></span>
                            <span class="arrow">›</span>
                        </div>
                    </div>
                </a>
            <?php endfor; ?>
        </div>
    </main>
</div>

<style>
    .back-btn {
        display:inline-flex; align-items:center; gap:6px;
        padding:8px 14px; border-radius:8px;
        background:#fff; color:#1f2937;
        border:1px solid #d1d5db;
        font-size:13px; font-weight:700; text-decoration:none;
        transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }
    .back-btn:hover { transform:translateX(-2px); border-color:#7c3aed; color:#7c3aed; box-shadow:0 4px 10px rgba(0,0,0,.06); }
    .week-card { display:block; text-decoration:none; color:inherit; }
    .week-card-inner {
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:12px;
        padding:18px 22px;
        box-shadow:0 4px 12px rgba(0,0,0,.04);
        transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .week-card:hover .week-card-inner {
        transform:translateY(-2px);
        box-shadow:0 12px 24px rgba(124,58,237,.16);
        border-color:#7c3aed;
    }
    .week-card.ready .week-card-inner { border-left:4px solid #10b981; }
    .week-head { display:flex; justify-content:space-between; align-items:start; }
    .week-num  { font-size:11px; font-weight:800; letter-spacing:1.5px; text-transform:uppercase; color:#7c3aed; }
    .week-title { font-size:17px; font-weight:700; color:#111827; margin-top:2px; }
    .week-meta {
        margin-top:14px;
        display:flex; gap:14px; align-items:center;
        font-size:12px; font-weight:700;
    }
    .meta-on  { color:#059669; }
    .meta-off { color:#9ca3af; }
    .week-meta .arrow { margin-left:auto; color:#7c3aed; font-weight:900; font-size:18px; transition:transform .15s ease; }
    .week-card:hover .arrow { transform:translateX(3px); }
    .proj-badge {
        font-size:10px; font-weight:800; letter-spacing:.4px;
        padding:3px 8px; border-radius:99px;
        background:#fef3c7; color:#92400e; border:1px solid #fde68a;
    }
</style>

<?php include('footer.php'); ?>
