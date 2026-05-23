<?php
/**
 * Cambridge-style curriculum browser — Level 4 of 4: Week content (SF view).
 *
 * URL: curriculum_week.php?CERT=<id>&TERM=<1|2|3>&MONTH=<1-12>&WEEK=<n>
 *
 * Read-only. School facilitators see whatever the Developer has uploaded:
 *   - PDF + video embedded for in-page viewing
 *   - Title + teacher notes if provided
 *
 * Adding / editing / uploading is intentionally NOT available here — it
 * lives on the Developer side (Auth/Developer/curriculum_week.php). The
 * `curriculum_weeks` row is keyed by certification_id only, so whatever
 * the Developer saves automatically appears in every school's SF view.
 */
include('header.php');
require_once(__DIR__ . '/../curriculum_helpers.php');

curriculum_ensure_table($conn);

$CERT  = isset($_GET['CERT'])  ? (int)$_GET['CERT']  : 0;
$TERM  = isset($_GET['TERM'])  ? (int)$_GET['TERM']  : 0;
$MONTH = isset($_GET['MONTH']) ? (int)$_GET['MONTH'] : 0;
$WEEK  = isset($_GET['WEEK'])  ? (int)$_GET['WEEK']  : 0;

if ($CERT <= 0 || !curriculum_valid_slot($TERM, $MONTH, $WEEK)) {
    echo '<p class="p-4">Bad slot. <a href="Current_Courses.php" class="text-blue-600 underline">Back to Current Courses</a>.</p>';
    include('footer.php'); exit;
}

$cstmt = $conn->prepare("SELECT certification_id, certification_name FROM certifications WHERE certification_id = ? LIMIT 1");
$cstmt->bind_param('i', $CERT);
$cstmt->execute();
$cert = $cstmt->get_result()->fetch_assoc();
$cstmt->close();

if (!$cert) {
    echo '<p class="p-4">Certification not found.</p>';
    include('footer.php'); exit;
}

$row = null;
$rstmt = $conn->prepare(
    "SELECT title, notes, bunny_pdf_url, bunny_video_url, updated_at
       FROM curriculum_weeks
      WHERE certification_id = ? AND term_number = ? AND month_number = ? AND week_number = ?
      LIMIT 1"
);
$rstmt->bind_param('iiii', $CERT, $TERM, $MONTH, $WEEK);
$rstmt->execute();
$row = $rstmt->get_result()->fetch_assoc();
$rstmt->close();

$terms     = curriculum_terms();
$names     = curriculum_month_names();
$title     = (string)($row['title']           ?? '');
$notes     = (string)($row['notes']           ?? '');
$pdf_url   = (string)($row['bunny_pdf_url']   ?? '');
$video_url = (string)($row['bunny_video_url'] ?? '');
$updated   = $row['updated_at'] ?? null;
$has_any   = ($pdf_url !== '' || $video_url !== '' || $notes !== '' || $title !== '');
?>

<div class="flex flex-1">
    <?php include('side_bar_courses.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">
        <div class="flex items-center gap-3 mb-3">
            <a href="curriculum_weeks.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$TERM ?>&MONTH=<?= (int)$MONTH ?>"
               class="back-btn">← Back to weeks in <?= htmlspecialchars($names[$MONTH]) ?></a>
        </div>
        <div class="text-xs text-gray-500 mb-2 uppercase tracking-wide font-semibold">
            <a href="Current_Courses.php" class="hover:text-blue-600">Current Courses</a>
            <span class="mx-1">›</span>
            <a href="curriculum_terms.php?CERT=<?= (int)$CERT ?>" class="hover:text-blue-600"><?= htmlspecialchars($cert['certification_name']) ?></a>
            <span class="mx-1">›</span>
            <a href="curriculum_months.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$TERM ?>" class="hover:text-blue-600"><?= htmlspecialchars($terms[$TERM]['label']) ?></a>
            <span class="mx-1">›</span>
            <a href="curriculum_weeks.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$TERM ?>&MONTH=<?= (int)$MONTH ?>" class="hover:text-blue-600"><?= htmlspecialchars($names[$MONTH]) ?></a>
            <span class="mx-1">›</span>
            <span class="text-gray-800">Week <?= (int)$WEEK ?></span>
        </div>

        <div class="flex justify-between items-end mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    <?php if ($title !== ''): ?>
                        <?= htmlspecialchars($title) ?>
                        <span class="text-base font-medium text-gray-500">· Week <?= (int)$WEEK ?>, <?= htmlspecialchars($names[$MONTH]) ?></span>
                    <?php else: ?>
                        Week <?= (int)$WEEK ?> <span class="text-gray-400">·</span> <?= htmlspecialchars($names[$MONTH]) ?>
                    <?php endif; ?>
                </h1>
                <p class="text-sm text-gray-500">
                    <?= htmlspecialchars($cert['certification_name']) ?> · <?= htmlspecialchars($terms[$TERM]['label']) ?> (<?= htmlspecialchars($terms[$TERM]['range']) ?>)
                    <?php if ($updated): ?>
                        <span class="ml-2">· last updated <?= htmlspecialchars($updated) ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if (!$has_any): ?>
            <div class="empty-state">
                <div class="empty-emoji">📭</div>
                <h2 class="text-lg font-bold text-gray-700">No content yet</h2>
                <p class="text-sm text-gray-500 mt-1">
                    The Developer hasn&rsquo;t uploaded material for this week yet.
                    Check back later or contact your admin if this seems wrong.
                </p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php if ($pdf_url !== ''): ?>
                    <div class="card">
                        <div class="card-h">📄 PDF</div>
                        <iframe src="<?= htmlspecialchars($pdf_url) ?>#toolbar=0" class="pdf-frame"></iframe>
                        <a href="<?= htmlspecialchars($pdf_url) ?>" target="_blank" class="text-xs text-blue-600 underline mt-2 inline-block">Open in new tab ↗</a>
                    </div>
                <?php endif; ?>

                <?php if ($video_url !== ''): ?>
                    <div class="card">
                        <div class="card-h">🎬 Video</div>
                        <video controls preload="metadata" class="video-frame">
                            <source src="<?= htmlspecialchars($video_url) ?>">
                            Your browser cannot play this video.
                        </video>
                        <a href="<?= htmlspecialchars($video_url) ?>" target="_blank" class="text-xs text-blue-600 underline mt-2 inline-block">Open in new tab ↗</a>
                    </div>
                <?php endif; ?>

                <?php if ($notes !== ''): ?>
                    <div class="card">
                        <div class="card-h">📝 Notes for the teacher</div>
                        <div class="whitespace-pre-wrap text-sm text-gray-700"><?= htmlspecialchars($notes) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
    .back-btn:hover { transform:translateX(-2px); border-color:#3b82f6; color:#3b82f6; box-shadow:0 4px 10px rgba(0,0,0,.06); }
    .card {
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:12px;
        padding:18px 20px;
        box-shadow:0 4px 12px rgba(0,0,0,.04);
    }
    .card-h {
        font-size:12px; font-weight:800; letter-spacing:1.5px; text-transform:uppercase;
        color:#3b82f6; margin-bottom:12px;
    }
    .pdf-frame   { width:100%; height:640px; border:1px solid #e5e7eb; border-radius:8px; }
    .video-frame { width:100%; max-height:480px; border-radius:8px; background:#000; }
    .empty-state {
        background:#fff; border:1px dashed #e5e7eb; border-radius:14px;
        padding:60px 30px; text-align:center;
        box-shadow:0 4px 12px rgba(0,0,0,.03);
    }
    .empty-emoji { font-size:48px; margin-bottom:14px; }
</style>

<?php include('footer.php'); ?>
