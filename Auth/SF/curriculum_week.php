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
    "SELECT cw_id, title, notes, bunny_pdf_url, bunny_video_url, updated_at
       FROM curriculum_weeks
      WHERE certification_id = ? AND term_number = ? AND month_number = ? AND week_number = ?
      LIMIT 1"
);
$rstmt->bind_param('iiii', $CERT, $TERM, $MONTH, $WEEK);
$rstmt->execute();
$row = $rstmt->get_result()->fetch_assoc();
$rstmt->close();

// cw_id is the primary key of curriculum_weeks. The proxies use it (rather
// than the Bunny URL) so the real CDN URL never appears in the page source.
$cw_id = (int)($row['cw_id'] ?? 0);

$terms     = curriculum_terms();
$names     = curriculum_month_names();
$title     = (string)($row['title']           ?? '');
$notes     = (string)($row['notes']           ?? '');
$pdf_url   = (string)($row['bunny_pdf_url']   ?? '');
$video_url = (string)($row['bunny_video_url'] ?? '');
$updated   = $row['updated_at'] ?? null;
$has_any   = ($pdf_url !== '' || $video_url !== '' || $notes !== '' || $title !== '');

// $this_year (calendar year) is set in session.php — used for the
// watermark so it auto-advances on rollover at year boundary.
$watermark_year = isset($this_year) && $this_year ? (int)$this_year : (int)date('Y');
$video_mime     = $video_url !== '' ? curriculum_video_mime_for_url($video_url) : 'video/mp4';
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

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
                    <div class="card no-grab">
                        <div class="card-h">📄 PDF</div>
                        <!-- Load the Bunny PDF straight into the iframe — same pattern
                             YouTube embeds use (the video file is also direct-from-CDN
                             when you embed a YT video on your site). The proxy approach
                             was killing on slow networks because Chrome's slow-network
                             intervention timed out the server-side curl fetch. Soft
                             protections (no Open-in-tab link, watermark, blocked
                             contextmenu / Ctrl+S / Ctrl+P) still raise the bar. -->
                        <div class="pdf-frame-wrap">
                            <iframe src="<?= htmlspecialchars($pdf_url) ?>#toolbar=0&navpanes=0"
                                    class="pdf-frame"></iframe>
                            <div class="pdf-watermark" aria-hidden="true">
                                <?php for ($i = 0; $i < 12; $i++): ?>
                                    <span>Confidential document · Copyright © <?= (int)$watermark_year ?></span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="protect-note">
                            🔒 This document is protected. Saving, printing, or sharing copies is not allowed.
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($video_url !== ''): ?>
                    <div class="card no-grab">
                        <div class="card-h">🎬 Video</div>
                        <!-- Try inline first. If the codec / container isn't supported
                             (most often .mov in Chrome) the <video> stays dead — that's
                             when the fallback button below kicks in. We also listen for
                             the `error` event so we surface a clear message instead of
                             just a silent broken player. -->
                        <video id="lesson-video" controls preload="metadata" playsinline
                               controlslist="nodownload noplaybackrate noremoteplayback"
                               disablepictureinpicture
                               class="video-frame">
                            <source src="<?= htmlspecialchars($video_url) ?>" type="<?= htmlspecialchars($video_mime) ?>">
                            Your browser cannot play this video inline. Use the
                            "Open video" button below.
                        </video>

                        <!-- Fallback access — the user explicitly asked: "if we cannot
                             stream on the site, let it direct us to the source". For
                             .mov files (and any other codec the browser refuses), this
                             button is the only way to view. Same model as embedded
                             YouTube falling back to youtube.com. -->
                        <div class="video-fallback">
                            <a href="<?= htmlspecialchars($video_url) ?>" target="_blank" rel="noopener"
                               class="video-open-btn">
                                ▶ Open video in a new window
                            </a>
                            <span id="video-hint" class="video-hint">
                                If the player above doesn&rsquo;t respond, use this button.
                            </span>
                        </div>

                        <p class="protect-note">
                            🔒 Please don&rsquo;t download or share copies of this video.
                        </p>
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
    .pdf-frame   { width:100%; height:640px; border:1px solid #e5e7eb; border-radius:8px; display:block; }
    .video-frame { width:100%; max-height:480px; border-radius:8px; background:#000; }

    /* PDF watermark overlay. Tiled diagonal text, rotated, semi-transparent.
       pointer-events: none lets the user still scroll / click links in the
       embedded PDF underneath. Not embedded INTO the PDF — just painted on
       this page, so it shows up only inside the LMS. */
    .pdf-frame-wrap { position:relative; overflow:hidden; border-radius:8px; }
    .pdf-watermark {
        position:absolute; inset:0;
        display:grid;
        grid-template-columns:repeat(3, 1fr);
        grid-auto-rows:120px;
        align-items:center; justify-items:center;
        transform:rotate(-28deg) scale(1.4);
        transform-origin:center;
        pointer-events:none;
        z-index:5;
    }
    .pdf-watermark span {
        color:rgba(220, 38, 38, .18);
        font-size:18px;
        font-weight:900;
        letter-spacing:.6px;
        text-transform:uppercase;
        white-space:nowrap;
        text-shadow:0 1px 0 rgba(255,255,255,.4);
        user-select:none;
    }
    .empty-state {
        background:#fff; border:1px dashed #e5e7eb; border-radius:14px;
        padding:60px 30px; text-align:center;
        box-shadow:0 4px 12px rgba(0,0,0,.03);
    }
    .empty-emoji { font-size:48px; margin-bottom:14px; }

    /* "Don't grab my content" — keep selection / drag tools off the
       preview cards. Doesn't affect the embedded PDF's own scroll behaviour. */
    .no-grab {
        user-select:none;
        -webkit-user-select:none;
        -webkit-user-drag:none;
    }
    .no-grab iframe, .no-grab video, .no-grab img {
        -webkit-user-drag:none;
    }
    .protect-note {
        margin-top:10px;
        font-size:12px; font-weight:700; color:#b91c1c;
        background:#fef2f2; border:1px solid #fecaca;
        padding:8px 12px; border-radius:8px;
        display:inline-block;
    }

    /* Video fallback row — shows a clear "open the source" button under
       the inline player. If inline plays, the button is just a backup. */
    .video-fallback {
        display:flex; align-items:center; flex-wrap:wrap; gap:12px;
        margin-top:12px;
    }
    .video-open-btn {
        display:inline-flex; align-items:center; gap:8px;
        padding:10px 18px; border-radius:8px;
        background:linear-gradient(135deg,#dc2626,#ef4444);
        color:#fff; font-weight:800; font-size:13px;
        text-decoration:none;
        box-shadow:0 6px 16px rgba(220,38,38,.25);
        transition:transform .12s ease, box-shadow .12s ease;
    }
    .video-open-btn:hover { transform:translateY(-1px); box-shadow:0 10px 22px rgba(220,38,38,.35); }
    .video-hint {
        font-size:12px; color:#6b7280; font-weight:600;
    }
    .video-hint.warn {
        color:#b45309;
        background:#fef3c7; border:1px solid #fde68a;
        padding:4px 10px; border-radius:6px;
        font-weight:700;
    }
</style>

<script>
// Best-effort content protection. None of this stops a determined attacker
// (browser devtools, screen recording, etc.) — but it stops casual save
// attempts: no context menu on the preview cards, no Ctrl/Cmd+S/P, no
// drag-to-desktop on the iframe/video.
(function () {
    const targets = document.querySelectorAll('.no-grab, .pdf-frame-wrap, .video-frame');
    targets.forEach((el) => {
        el.addEventListener('contextmenu', (e) => e.preventDefault());
        el.addEventListener('dragstart',   (e) => e.preventDefault());
        el.addEventListener('selectstart', (e) => e.preventDefault());
    });

    // Block Save / Print shortcuts on the curriculum page itself. PDFs
    // rendered inside the iframe have their own document — these listeners
    // on `document` only fire when focus is on our page chrome. Mainly to
    // stop a user pressing Ctrl+S while the page is in focus.
    document.addEventListener('keydown', (e) => {
        const k = (e.key || '').toLowerCase();
        if ((e.ctrlKey || e.metaKey) && (k === 's' || k === 'p')) {
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);

    // Surface a clearer message when the browser refuses the video codec
    // (most common: .mov in Chrome → silent dead player). Listen for the
    // <video> error event and highlight the fallback button.
    const vid = document.getElementById('lesson-video');
    if (vid) {
        const showFallback = () => {
            const hint = document.getElementById('video-hint');
            if (hint) {
                hint.textContent = '⚠️ This video can’t play inline here — click the button to open it.';
                hint.classList.add('warn');
            }
        };
        vid.addEventListener('error', showFallback, true);
        // <source> error doesn't bubble to <video>, listen separately:
        const src = vid.querySelector('source');
        if (src) src.addEventListener('error', showFallback);
    }
})();
</script>

<?php include('footer.php'); ?>
