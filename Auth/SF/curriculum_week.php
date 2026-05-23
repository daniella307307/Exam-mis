<?php
/**
 * Cambridge-style curriculum browser — Level 4 of 4: Week content.
 *
 * URL: curriculum_week.php?CERT=<id>&TERM=<1|2|3>&MONTH=<1-12>&WEEK=<n>
 *
 * Two-panel view:
 *   - left/top:  PDF + video preview from Bunny CDN (when URLs are saved)
 *   - right/bottom: editor form to paste/update Bunny URLs + title + notes
 *
 * The form posts to save_curriculum_week.php which upserts by the
 * (certification, term, month, week) UNIQUE key, so the very first save
 * creates the row and subsequent saves edit it.
 */
include('header.php');
require_once(__DIR__ . '/curriculum_helpers.php');

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

// Load existing row (if any).
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

$terms      = curriculum_terms();
$names      = curriculum_month_names();
$title      = (string)($row['title']           ?? '');
$notes      = (string)($row['notes']           ?? '');
$pdf_url    = (string)($row['bunny_pdf_url']   ?? '');
$video_url  = (string)($row['bunny_video_url'] ?? '');
$updated    = $row['updated_at'] ?? null;
?>

<div class="flex flex-1">
    <?php include('side_bar_courses.php'); ?>

    <main class="bg-gray-50 flex-1 p-6 overflow-y-auto">
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
                    Week <?= (int)$WEEK ?> <span class="text-gray-400">·</span> <?= htmlspecialchars($names[$MONTH]) ?>
                </h1>
                <p class="text-sm text-gray-500">
                    <?= htmlspecialchars($cert['certification_name']) ?> · <?= htmlspecialchars($terms[$TERM]['label']) ?> (<?= htmlspecialchars($terms[$TERM]['range']) ?>)
                    <?php if ($updated): ?>
                        <span class="ml-2">· last updated <?= htmlspecialchars($updated) ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <a href="curriculum_weeks.php?CERT=<?= (int)$CERT ?>&TERM=<?= (int)$TERM ?>&MONTH=<?= (int)$MONTH ?>"
               class="text-xs text-gray-500 hover:text-blue-600 underline">← Back to weeks</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
            <!-- LEFT: Preview pane -->
            <div class="lg:col-span-3 space-y-4">
                <div class="card">
                    <div class="card-h">📄 PDF preview</div>
                    <?php if ($pdf_url !== ''): ?>
                        <iframe src="<?= htmlspecialchars($pdf_url) ?>#toolbar=0" class="pdf-frame"></iframe>
                        <a href="<?= htmlspecialchars($pdf_url) ?>" target="_blank" class="text-xs text-blue-600 underline mt-2 inline-block">Open in new tab ↗</a>
                    <?php else: ?>
                        <div class="empty">No PDF yet. Paste a Bunny URL on the right to attach one.</div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-h">🎬 Video preview</div>
                    <?php if ($video_url !== ''): ?>
                        <video controls preload="metadata" class="video-frame">
                            <source src="<?= htmlspecialchars($video_url) ?>">
                            Your browser cannot play this video.
                        </video>
                        <a href="<?= htmlspecialchars($video_url) ?>" target="_blank" class="text-xs text-blue-600 underline mt-2 inline-block">Open in new tab ↗</a>
                    <?php else: ?>
                        <div class="empty">No video yet. Paste a Bunny URL on the right to attach one.</div>
                    <?php endif; ?>
                </div>

                <?php if ($notes !== ''): ?>
                    <div class="card">
                        <div class="card-h">📝 Notes</div>
                        <div class="whitespace-pre-wrap text-sm text-gray-700"><?= htmlspecialchars($notes) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- RIGHT: Editor -->
            <aside class="lg:col-span-2">
                <form id="cw-form" class="card sticky-pane">
                    <div class="card-h">✏️ Manage this week</div>

                    <label class="form-lbl">Week title</label>
                    <input type="text" name="title" maxlength="255"
                           value="<?= htmlspecialchars($title) ?>"
                           placeholder="e.g. Counting 1–10"
                           class="form-in">

                    <label class="form-lbl">📄 Bunny PDF URL</label>
                    <input type="url" name="bunny_pdf_url"
                           value="<?= htmlspecialchars($pdf_url) ?>"
                           placeholder="https://yourzone.b-cdn.net/.../week1.pdf"
                           class="form-in">

                    <label class="form-lbl">🎬 Bunny video URL</label>
                    <input type="url" name="bunny_video_url"
                           value="<?= htmlspecialchars($video_url) ?>"
                           placeholder="https://yourzone.b-cdn.net/.../week1.mp4"
                           class="form-in">

                    <label class="form-lbl">Notes for the teacher (optional)</label>
                    <textarea name="notes" rows="4"
                              placeholder="Lesson plan, talking points, prep…"
                              class="form-in"><?= htmlspecialchars($notes) ?></textarea>

                    <button type="submit" class="save-btn">
                        💾 Save week content
                    </button>
                    <div id="cw-status" class="text-xs mt-2 min-h-[1em]"></div>
                </form>
            </aside>
        </div>
    </main>
</div>

<style>
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
    .pdf-frame   { width:100%; height:520px; border:1px solid #e5e7eb; border-radius:8px; }
    .video-frame { width:100%; max-height:380px; border-radius:8px; background:#000; }
    .empty {
        padding:30px; text-align:center; color:#9ca3af;
        background:#f9fafb; border:1px dashed #e5e7eb; border-radius:8px;
        font-size:13px;
    }
    .sticky-pane { position:sticky; top:14px; }
    .form-lbl { display:block; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:#6b7280; margin:14px 0 6px; }
    .form-in {
        width:100%; padding:10px 12px;
        border:1.5px solid #e5e7eb; border-radius:8px;
        font-size:14px; color:#111827; background:#fff;
        transition:border-color .15s ease, box-shadow .15s ease;
    }
    .form-in:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 4px rgba(59,130,246,.12); }
    .save-btn {
        margin-top:18px; width:100%;
        padding:12px 16px; border:none; border-radius:8px; cursor:pointer;
        background:linear-gradient(135deg,#3b82f6,#1d4ed8); color:#fff;
        font-weight:800; font-size:14px; letter-spacing:.3px;
        box-shadow:0 8px 22px rgba(59,130,246,.35);
        transition:transform .15s ease;
    }
    .save-btn:hover { transform:translateY(-1px); }
    .save-btn:disabled { opacity:.6; cursor:wait; }
</style>

<script>
document.getElementById('cw-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form  = e.currentTarget;
    const btn   = form.querySelector('.save-btn');
    const stat  = document.getElementById('cw-status');
    const fd    = new FormData(form);
    fd.append('cert',  '<?= (int)$CERT ?>');
    fd.append('term',  '<?= (int)$TERM ?>');
    fd.append('month', '<?= (int)$MONTH ?>');
    fd.append('week',  '<?= (int)$WEEK ?>');

    btn.disabled = true;
    stat.textContent = 'Saving…';
    stat.style.color = '#6b7280';
    try {
        const r = await fetch('save_curriculum_week.php', { method: 'POST', body: fd });
        const j = await r.json();
        if (!j.success) throw new Error(j.error || 'save failed');
        stat.textContent = '✅ Saved. Reloading preview…';
        stat.style.color = '#059669';
        setTimeout(() => location.reload(), 700);
    } catch (err) {
        stat.textContent = '❌ ' + err.message;
        stat.style.color = '#dc2626';
    } finally {
        btn.disabled = false;
    }
});
</script>

<?php include('footer.php'); ?>
